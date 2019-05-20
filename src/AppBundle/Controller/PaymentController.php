<?php
namespace AppBundle\Controller;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Topping;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PDFWriter;
use AppBundle\Services\RewardSpendingService;
use AppBundle\Services\SponsorshipService;
use AppBundle\Services\TicketingManager;
use Psr\Log\LoggerInterface;
use Spipu\Html2Pdf\Html2Pdf;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Translation\TranslatorInterface;
/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PaymentController extends BaseController
{
    /**
     * @Route("/cart/{id}/payment/3ds/post", name="user_cart_payment_3DS_stripe_post")
     */
    public function cart3DSPostAction(Request $request, Cart $cart, UserInterface $user) {
        $payment_intent_id = $request->get('payment_intent_id');
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
        $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        $intent->confirm();
        return $this->generatePaymentResponse($intent, $cart);
    }
    /**
     * @Route("/cart/payment/bancontact", name="user_cart_payment_bancontact_stripe")
     */
    public function bancontactPostAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        /** @var Cart $cart */
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->isPaid()) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }
        $amount = intval($_POST['amount']);
        // We set an explicit test for amount changes as it has legal impacts
        if (floatval($amount) !=  floatval($cart->getAmount() * 100)) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->render('@App/User/pay_cart.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }
        foreach($cart->getContracts() as $cf) {
            /** @var ContractFan $cf */
            /** @var ContractArtist $contract_artist */
            $contract_artist = $cf->getContractArtist();
            if ($contract_artist->isUncrowdable()) {
                $this->addFlash('error', 'errors.event_uncrowdable');
                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
            }
            foreach($cf->getPurchases() as $purchase) {
                if($contract_artist->getNbAvailable($purchase->getCounterpart()) < $purchase->getQuantityOrganic()) {
                    $this->addFlash('error', 'errors.order_max');
                    return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
                }
            }
        }
        $em->flush();
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
        // Token is created using Stripe.js or Checkout!
        // Get the payment token submitted by the form:
        $source = $_POST['stripeSource'];
        // Charge the user's card:
        try {
            foreach($cart->getContracts() as $contract) {
                /** @var ContractFan $contract
                 * @var ContractArtist $contract_artist */
                $contract->calculatePromotions();
                $contract_artist = $contract->getContractArtist();
                $contract_artist->addAmount($contract->getAmount());
                if ($contract_artist instanceof ContractArtist) {
                    $contract_artist->updateCounterPartsSold($contract);
                }
            }
            $payment = new Payment();
            $payment->setDate(new \DateTime())->setUser($user)
                ->setCart($cart)->setRefunded(false)->setAmount($cart->getAmount());
            // $em->detach($cart); // Otherwise a payment error would still let the tickets be considered as paid in crowdfunding advancement
            $charge = \Stripe\Charge::create(array(
                "amount" => $amount,
                "currency" => "eur",
                "description" => "Un-Mute - payment " . $cart->getId(),
                "source" => $source,
            ));
            $payment->setChargeId($charge->id);
            $em->persist($payment);
            $cart->setPaid(true);
            /* foreach($cart->getContracts() as $contract) {
                 $contract_artist = $contract->getContractArtist();
                 //reward
                 $rewardSpendingService->consumeReward($contract);
                 //sponsorship
                 $sponsorship = $sponsorshipService->giveSponsorshipRewardOnPurchaseIfPossible($user, $contract_artist);
             }*/
            $em->persist($cart);
            $em->flush();
            return $this->redirectToRoute('user_cart_payment_stripe_success', array('cart_code' => $cart->getBarcodeText())); //, 'sponsorship' => $sponsorship));
        } catch (\Stripe\Error\Card $e) {
            $this->addFlash('error', 'errors.stripe.card');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\RateLimit $e) {
            $this->addFlash('error', 'errors.stripe.rate_limit');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->addFlash('error', 'errors.stripe.invalid_request');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\Authentication $e) {
            $this->addFlash('error', 'errors.stripe.authentication');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->addFlash('error', 'errors.stripe.api_connection');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\Base $e) {
            $this->addFlash('error', 'errors.stripe.generic');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Exception $e) {
            $this->addFlash('error', 'errors.stripe.other');
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        }
        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ));
    }
    /**
     * @Route("/cart/{id}/payment/post", name="user_cart_payment_stripe_post")
     */
    public function cartPostAction(Request $request, Cart $cart, UserInterface $user) {
        $translator = $this->get('translator');
        $amount = intval($request->get('amount'));
        $payment_method_id = $request->get('payment_method_id');
        $em = $this->em;
        // We set an explicit test for amount changes as it has legal impacts
        if (floatval($amount) !=  floatval($cart->getAmount() * 100)) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->render('@App/User/pay_cart.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }
        foreach($cart->getContracts() as $cf) {
            /** @var ContractFan $cf */
            /** @var ContractArtist $contract_artist */
            $contract_artist = $cf->getContractArtist();
            if ($contract_artist->isUncrowdable()) {
                $this->addFlash('error', 'errors.event_uncrowdable');
                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
            }
            foreach($cf->getPurchases() as $purchase) {
                if($contract_artist->getNbAvailable($purchase->getCounterpart()) < $purchase->getQuantityOrganic()) {
                    $this->addFlash('error', 'errors.order_max');
                    return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
                }
            }
        }
        $em->flush();
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
        try {
            foreach($cart->getContracts() as $contract) {
                /** @var ContractFan $contract
                 * @var ContractArtist $contract_artist */
                $contract->calculatePromotions();
                $contract_artist = $contract->getContractArtist();
                $contract_artist->addAmount($contract->getAmount());
                if ($contract_artist instanceof ContractArtist) {
                    $contract_artist->updateCounterPartsSold($contract);
                }
            }
            $intent = null;
            # Create the PaymentIntent
            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $payment_method_id,
                'amount' => $amount,
                'currency' => 'eur',
                'confirmation_method' => 'manual',
                'confirm' => true,
                "description" => "Un-Mute - payment " . $cart->getId(),
            ]);
            $cart->generateBarCode();
            $em->persist($cart);
            $em->flush();
            return $this->generatePaymentResponse($intent, $cart);
        } catch (\Stripe\Error\Card $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.card')]);
        } catch (\Stripe\Error\RateLimit $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.rate_limit')]);
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.invalid_request')]);
        } catch (\Stripe\Error\Authentication $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.authentication')]);
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.api_connection')]);
        } catch (\Stripe\Error\Base $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.generic')]);
        } catch (\Exception $e) {
            $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            return $this->json(['error' => $translator->trans('errors.stripe.other')]);
        }
    }
    /**
     * @Route("/cart/payment", name="user_cart_payment_stripe")
     */
    public function cartAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        /** @var Cart $cart */
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->isPaid()) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }
        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ));
    }
    function generatePaymentResponse(PaymentIntent $intent, Cart $cart) {
        if ($intent->status == 'requires_action' &&
            $intent->next_action->type == 'use_stripe_sdk') {
            # Tell the client to handle the action
            return $this->json([
                'requires_action' => true,
                'barcode' => $cart->getBarcodeText(),
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        } else if ($intent->status == 'succeeded') {
            # The payment didn’t need any additional actions and completed!
            # Handle post-payment fulfillment
            $cart->setPaid(true);
            $payment = new Payment();
            $payment->setDate(new \DateTime())->setUser($cart->getUser())
                ->setCart($cart)->setRefunded(false)->setAmount($cart->getAmount());
            try {
                $payment->setChargeId($intent->charges->data[0]->id);
            } catch(\Throwable $exception) {
                $payment->setChargeId($intent->id);
            }
            $this->em->persist($cart);
            $this->em->persist($payment);
            $this->em->flush();
            return $this->json([
                "success" => true,
                'barcode' => $cart->getBarcodeText(),
            ]);
        } else {
            # Invalid status
            return $this->json(['error' => $this->get('translator')->trans('errors.stripe.other')]);
        }
    }
    /**
     * @Route("/cart/payment/success/", name="user_cart_payment_stripe_success")
     */
    public function cartSuccessAction(Request $request, TranslatorInterface $translator, PDFWriter $writer)
    {
        $em = $this->getDoctrine()->getManager();
        $cart_code = $request->get('cart_code', null);
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $cart_code]);
        if(!$cart->isPaid() || $cart->getConfirmed()) {
            throw $this->createNotFoundException();
        }
        $this->addFlash('notice', $translator->trans('notices.payment'));
        if ($request->get('sponsorship')) {
            $this->addFlash('notice', $translator->trans('notices.sponsorship.cart_success', []));
        }
        $writer->writeOrder($cart);
        $cart->setConfirmed(true);
        $em->flush();
        return $this->redirectToRoute('user_cart_send_order_recap', ['cart_code' => $cart->getBarcodeText(), 'is_payment' => true]);
    }
    /**
     * @Route("/cart/send-recap-{cart_code}", name="user_cart_send_order_recap")
     */
    public function sendOrderRecap(Request $request, $cart_code, MailDispatcher $dispatcher, TicketingManager $ticketingManager)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $cart_code]);
        if(!$cart->isPaid() || !$cart->getConfirmed() || $cart->getFinalized()) {
            throw $this->createNotFoundException();
        }
        $dispatcher->sendOrderRecap($cart);
        foreach($cart->getContracts() as $cf) {
            if ($cf->getContractArtist() instanceof ContractArtist && $cf->getContractArtist()->getCounterPartsSent()) {
                $ticketingManager->sendUnSentTicketsForContractFan($cf);
            }
        }
        $cart->setFinalized(true);
        $em->flush();
        return $this->redirectToRoute('user_paid_carts',['is_payment' => false]); // $request->get('is_payment')]);
    }
    /**
     * @Route("/cart/payment/pending", name="user_cart_payment_pending")
     */
    public function cartPendingAction(Request $request, UserInterface $user, $cart_code)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $cart_code]);
        /** @var Cart $cart */
        if ($cart == null || count($cart->getContracts()) == 0) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }
        $source = $request->get('source');
        $client_secret = $request->get('client_secret');
        return $this->render('@App/User/threeds_pending.html.twig', array(
            'cart' => $cart,
            'source' => $source,
            'client_secret' => $client_secret,
        ));
    }
}