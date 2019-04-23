<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Entity\YB\YBContact;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBOrder;
use AppBundle\Form\ContractFanType;
use AppBundle\Form\YB\YBContactType;
use AppBundle\Services\CaptchaManager;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\PaymentIntent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class YBController extends BaseController
{
    /**
     * @Route("/", name="yb_index")
     */
    public function indexAction(Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher, CaptchaManager $captchaManager)
    {
        $contact = new YBContact();
        $form = $this->createForm(YBContactType::class, $contact, ['action' => $this->generateUrl('yb_index') . '#contact']);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
           if(!$captchaManager->verify()) {
               $this->addFlash('error', 'Le test anti-robots a échoué... seriez-vous un androïde ??? Veuillez réessayer !');
                return $this->render('@App/YB/home.html.twig', [
                    'form' => $form->createView(),
                ]);
           }

            // DB save
            $em->persist($contact);
            $em->flush();

            // Mail
            $mailDispatcher->sendYBContactCopy($contact);
            $mailDispatcher->sendAdminYBContact($contact);

            $this->addFlash('yb_notice', 'Merci pour votre message. Nous vous recontacterons aussi vite que possible.');
            return $this->redirectToRoute('yb_index');
        }

        return $this->render('@App/YB/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/campaign/{id}/{slug}", name="yb_campaign")
     */
    public function campaignAction(YBContractArtist $c, EntityManagerInterface $em, Request $request, ValidatorInterface $validator, $slug = null) {

        if($slug != null && $c->getSlug() != $slug) {
            return $this->redirectToRoute('yb_campaign', ['id' => $c->getId(), 'slug' => $c->getSlug()]);
        }

        $cf = new ContractFan($c);
        $form = $this->createForm(ContractFanType::class, $cf, ['user_rewards' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $cart = new Cart(false);

            foreach($cf->getPurchases() as $purchase) {
                if($purchase->getQuantity() == 0) {
                    $cf->removePurchase($purchase);
                }
            }

            $cf->initAmount();

            $cart->addContract($cf);
            $cart->generateBarCode();

            $em->persist($cart);
            $em->flush();

            return $this->redirectToRoute('yb_checkout', ['code' => $cart->getBarcodeText()]);
        }

        return $this->render('@App/YB/campaign.html.twig', [
            'campaign' => $c,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/conditions", name="yb_terms")
     */
    public function termsAction() {

        return $this->render('@App/YB/terms.html.twig', [

        ]);
    }

    /**
     * @Route("/checkout/bancontact/{code}", name="yb_bancontact_checkout")
     */
    public function bancontactCheckoutAction(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, $code) {

        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);

        $amount = intval($_POST['amount']);
        // We set an explicit test for amount changes as it has legal impacts
        if (floatval($amount) !=  floatval($cart->getAmount() * 100)) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->render('@App/YB/checkout.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }

        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        if($cart->getYbOrder() == null) {
            $order = new YBOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name)->setCart($cart);
            $cart->setYbOrder($order);
        }
        else {
            $order = $cart->getYbOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name);
            $em->persist($order);
        }

        $errors = $validator->validate($order);
        if(count($errors) > 0) {
            $this->addFlash('error', 'errors.order_coords');
            return $this->render('@App/YB/checkout.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }

        foreach($cart->getContracts() as $cf) {
            /** @var ContractFan $cf */
            /** @var YBContractArtist $contract_artist */
            $contract_artist = $cf->getContractArtist();
            if ($contract_artist->isUncrowdable()) {
                $this->addFlash('error', 'errors.event_uncrowdable');
                return $this->redirectToRoute('yb_campaign', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
            }

            foreach ($cf->getPurchases() as $purchase) {
                if ($contract_artist->getNbAvailable($purchase->getCounterpart()) < $purchase->getQuantityOrganic()) {
                    $this->addFlash('error', 'errors.order_max');
                    return $this->redirectToRoute('yb_campaign', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
                }
            }
        }

        $em->persist($order);
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
                 * @var YBContractArtist $contract_artist */
                $contract->calculatePromotions();
            }

            $payment = new Payment();
            $payment->setDate(new \DateTime())->setUser(null)
                ->setCart($cart)->setRefunded(false)->setAmount($cart->getAmount());

            $charge = \Stripe\Charge::create(array(
                "amount" => $amount,
                "currency" => "eur",
                "description" => "Ticked-it - payment " . $cart->getId(),
                "source" => $source,
            ));

            $payment->setChargeId($charge->id);
            $em->persist($payment);

            $em->persist($cart);
            return $this->redirectToRoute('yb_payment_success', array('code' => $cart->getBarcodeText())); //, 'sponsorship' => $sponsorship));

        } catch (\Stripe\Error\Card $e) {
            $this->addFlash('error', 'errors.stripe.card');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\RateLimit $e) {
            $this->addFlash('error', 'errors.stripe.rate_limit');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->addFlash('error', 'errors.stripe.invalid_request');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\Authentication $e) {
            $this->addFlash('error', 'errors.stripe.authentication');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->addFlash('error', 'errors.stripe.api_connection');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Stripe\Error\Base $e) {
            $this->addFlash('error', 'errors.stripe.generic');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        } catch (\Exception $e) {
            $this->addFlash('error', 'errors.stripe.other');
            // $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
        }
        return $this->json([]);
    }

    /**
     * @Route("/cart/{code}/payment/3ds/post", name="yb_payment_3DS_stripe_post")
     */
    public function cart3DSPostAction(Request $request, $code, UserInterface $user, EntityManagerInterface $em) {
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);
        $payment_intent_id = $request->get('payment_intent_id');

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
        $intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        $intent->confirm();

        return $this->generatePaymentResponse($intent, $cart);
    }

    /**
     * @Route("/cart/{code}/payment/post", name="yb_cart_payment_stripe_post")
     */
    public function cartPostAction(Request $request, $code, UserInterface $user, ValidatorInterface $validator) {

        $amount = intval($request->get('amount'));
        $payment_method_id = $request->get('payment_method_id');

        $em = $this->em;
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);

        // We set an explicit test for amount changes as it has legal impacts
        if (floatval($amount) !=  floatval($cart->getAmount() * 100)) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->render('@App/YB/checkout.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }

        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];

        if($cart->getYbOrder() == null) {
            $order = new YBOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name)->setCart($cart);
            $cart->setYbOrder($order);
        }

        else {
            $order = $cart->getYbOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name)->setCart($cart);
            $em->persist($order);
        }

        $errors = $validator->validate($order);
        if(count($errors) > 0) {
            $this->addFlash('error', 'errors.order_coords');
            return $this->render('@App/YB/checkout.html.twig', array(
                'cart' => $cart,
                'error_conditions' => false,
            ));
        }

        foreach($cart->getContracts() as $cf) {
            /** @var ContractFan $cf */
            /** @var YBContractArtist $contract_artist */
            $contract_artist = $cf->getContractArtist();
            if ($contract_artist->isUncrowdable()) {
                $this->addFlash('error', 'errors.event_uncrowdable');
                return $this->redirectToRoute('yb_campaign', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
            }

            foreach ($cf->getPurchases() as $purchase) {
                if ($contract_artist->getNbAvailable($purchase->getCounterpart()) < $purchase->getQuantityOrganic()) {
                    $this->addFlash('error', 'errors.order_max');
                    return $this->redirectToRoute('yb_campaign', ['id' => $contract_artist->getId(), 'slug' => $contract_artist->getSlug()]);
                }
            }
        }

        $em->persist($order);
        $em->flush();
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

        try {
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
            $payment->setChargeId($intent->id);
            $this->em->persist($cart);
            $this->em->persist($payment);
            $this->em->flush();
            return $this->json([
                "success" => true,
                'barcode' => $cart->getBarcodeText(),
            ]);
        } else {
            # Invalid status
            return $this->json(['error' => 'Invalid PaymentIntent status']);
        }
    }


    /**
     * @Route("/checkout/{code}", name="yb_checkout")
     */
    public function checkoutAction(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, $code) {
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);

        /** @var Cart $cart */
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->getPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        return $this->render('@App/YB/checkout.html.twig', [
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ]);
    }

    /**
     * @Route("payment/pending/{code}", name="yb_cart_payment_pending")
     */
    public function paymentPendingAction(Request $request, EntityManagerInterface $em, $code)
    {
        /** @var Cart $cart */
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);

        if ($cart == null || count($cart->getContracts()) == 0 || $cart->getPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        $source = $request->get('source');
        $client_secret = $request->get('client_secret');

        return $this->render('@App/YB/payment_pending.html.twig', array(
            'cart' => $cart,
            'source' => $source,
            'client_secret' => $client_secret,
        ));
    }

    /**
     * @Route("/payment/success", name="yb_payment_success")
     */
    public function paymentSuccessAction(MailDispatcher $mailDispatcher, TicketingManager $ticketingManager, EntityManagerInterface $em, Request $request) {

        $code = $request->get('code');
        /** @var Cart $cart */
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code]);
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->getFinalized() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        // Send order recap
        $mailDispatcher->sendYBOrderRecap($cart);

        foreach($cart->getContracts() as $contract) {
            /** @var YBContractArtist $campaign */
            $campaign = $contract->getContractArtist();

            $campaign->addAmount($contract->getAmount());
            $campaign->updateCounterPartsSold($contract);

            // Need to also send tickets
            if($campaign->isEvent() && ($campaign->getSuccessful() || $campaign->getTicketsSent() || $campaign->hasNoThreshold())) {
                $ticketingManager->generateAndSendYBTickets($contract);
            }

            $em->persist($campaign);
        }

        $cart->setFinalized(true);
        $em->flush();

        $this->addFlash('yb_notice', 'Paiement bien reçu ! Votre commande est validée. Vous devriez avoir reçu un récapitulatif par e-mail.');

        return $this->redirectToRoute('yb_order', ['code' => $cart->getBarCodeText()]);
    }

    /**
     * @Route("/ticked-it-order/{code}", name="yb_order")
     */
    public function orderAction(EntityManagerInterface $em, $code, TicketingManager $ticketingManager) {

        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $code, 'paid' => true]);

        foreach($cart->getContracts() as $cf) {
            /** @var ContractFan $cf */
            if($cf->getContractArtist()->getTicketsSent() && !$cf->getcounterpartsSent())
                $ticketingManager->generateAndSendYBTickets($cf);
        }

        return $this->render('AppBundle:YB:order.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/ticked-it-tickets/{code}", name="yb_get_tickets")
     */
    public function getTicketsAction(EntityManagerInterface $em, TicketingManager $ticketingManager, $code) {

        $contract = $em->getRepository('AppBundle:ContractFan')->findOneBy(['barcode_text' => $code]);

        if ($contract->isRefunded() || !$contract->getContractArtist()->getTicketsSent()) {
            throw $this->createAccessDeniedException();
        }

        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getTicketsPath();
        $finder->files()->name($contract->getTicketsFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::YB_TICKETS_DIRECTORY);

        if (count($finder) == 0) {
            $contract->setcounterpartsSent(false);
            $ticketingManager->generateAndSendYBTickets($contract);
            $contract->setcounterpartsSent(true);

            $em->persist($contract);
            $em->flush();
            $finder = new Finder();
            $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getTicketsPath();
            $finder->files()->name($contract->getTicketsFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::YB_TICKETS_DIRECTORY);
        }

        foreach ($finder as $file) {
            $response = new BinaryFileResponse($filePath);
            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', 'PDF');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ticked-it-tickets.pdf'
            ));
            return $response;
        }
    }

    /**
     * @Route("/api/submit-order-coordinates", name="yb_ajax_post_order")
     */
    public function orderAjaxAction(EntityManagerInterface $em, Request $request, ValidatorInterface $validator, MailDispatcher $mailDispatcher) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $cart_code = $_POST['cart_code'];

        /** @var Cart $cart */
        $cart = $em->getRepository('AppBundle:Cart')->findOneBy(['barcode_text' => $cart_code]);
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->getPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        if($cart->getYbOrder() == null) {
            $order = new YBOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name)->setCart($cart);
            $cart->setYbOrder($order);
        }
        else {
            $order = $cart->getYbOrder();
            $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name);
            $em->persist($order);
        }
        
        $errors = $validator->validate($order);
        if($errors->count() > 0) {
            throw new \Exception($errors->offsetGet(0));
        }

        if($cart->isFree()) {
            $cart->setPaid(true);
            $mailDispatcher->sendYBOrderRecap($cart);
        }

        $em->persist($order);
        $em->persist($cart);
        $em->flush();

        return new Response(' ', 200);
    }

    /**
     * @Route("/signin", name="yb_login")
     */
    public function loginAction(Request $request, CsrfTokenManagerInterface $tokenManager = null, UserInterface $user = null)
    {
        if($user != null) {
            //$this->addFlash('yb_notice', "Vous êtes bien connecté !");
            return $this->redirectToRoute('yb_members_dashboard');
        }

        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $tokenManager
            ? $tokenManager->getToken('authenticate')->getValue()
            : null;

        return $this->render('@App/YB/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    /**
     * @Route("/signout", name="yb_logout")
     */
    public function logoutAction(Request $request, TokenStorageInterface $tokenStorage) {
        $tokenStorage->setToken(null);
        $session = $request->getSession();
        $session->invalidate();
        $response = new RedirectResponse($this->generateUrl('yb_index'));
        $cookieNames = [
            $this->getParameter('session_name'),
            $this->getParameter('remember_me_name'),
        ];
        foreach ($cookieNames as $cookieName) {
            $response->headers->clearCookie($cookieName);
        }
        $this->addFlash('yb_notice', "Vous êtes bien déconnecté.");
        return $response;
    }

}
