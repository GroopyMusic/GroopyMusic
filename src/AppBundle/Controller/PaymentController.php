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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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

        if ($request->getMethod() == 'POST' && $_POST['accept_conditions']) {

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
        }

        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ));
    }

    /**
     * @Route("/cart/payment/success/{cart_code}", name="user_cart_payment_stripe_success")
     */
    public function cartSuccessAction(Request $request, $cart_code, TranslatorInterface $translator, PDFWriter $writer)
    {
        $em = $this->getDoctrine()->getManager();

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
