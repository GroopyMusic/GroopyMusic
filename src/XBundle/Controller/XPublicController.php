<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Services\CaptchaManager;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use XBundle\Entity\Project;
use XBundle\Entity\XCart;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XPurchase;
use XBundle\Entity\XCategory;
use XBundle\Entity\XContact;
use XBundle\Entity\XOrder;
use XBundle\Entity\XPayment;
use XBundle\Form\XContractFanType;
use XBundle\Form\XContactType;
use XBundle\Form\DonationType;


class XPublicController extends BaseController
{
    /**
     * @Route("/", name="x_homepage")
     */
    public function indexAction(EntityManagerInterface $em, Request $request, MailDispatcher $mailDispatcher, CaptchaManager $captchaManager)
    {
        // à changer pour afficher que les populaires et courant
        $projects = $em->getRepository('XBundle:Project')->findValidatedProjects();
        
        $contact = new XContact();
        $form = $this->createForm(XContactType::class, $contact, ['action' => $this->generateUrl('x_homepage') . '#contact']);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if(!$captchaManager->verify()) {
                $this->addFlash('error', 'Le test anti-robots a échoué... seriez-vous un androïde ??? Veuillez réessayer !');
                 return $this->render('@X/XPublic/home.html.twig', [
                     'form' => $form->createView(),
                 ]);
            }

            $em->persist($contact);
            $em->flush();
 
            // Mail
            $mailDispatcher->sendAdminXContact($contact);
            $mailDispatcher->sendXContactCopy($contact);
 
            $this->addFlash('x_notice', 'Merci pour votre message. Nous vous recontacterons aussi vite que possible.');
            return $this->redirectToRoute('x_homepage');
        }
        
        return $this->render('@X/XPublic/home.html.twig', array(
            'form' => $form->createView(),
            'projects' => $projects,
        ));
    }


    /**
     * @Route("/projects", name="x_projects")
     */
    public function projectsAction(EntityManagerInterface $em)
    {
        $projects = $em->getRepository('XBundle:Project')->findValidatedProjects();
        $categories = $em->getRepository('XBundle:XCategory')->findAll();

        return $this->render('@X/XPublic/catalog_projects.html.twig', array(
            'projects' => $projects,
            'categories' => $categories
        ));
    }


    /**
     * @Route("/project/{id}-{slug}", name="x_project")
     */
    public function projectAction(EntityManagerInterface $em, Request $request, Project $project, $slug = null)
    {
        if($slug != null && $project->getSlug() != $slug) {
            return $this->redirectToRoute('x_project', ['id' => $project->getId(), 'slug' => $project->getSlug()]);
        }

        if($project->getDeletedAt() != null) {
            return $this->redirectToRoute('x_homepage');
        }

        $hasProducts = $project->hasValidatedProducts();

        $contribution = new XContractFan($project);

        $form = $this->createForm(DonationType::class, $contribution);
        $form->handleRequest($request);

        $formPurchase = $this->createForm(XContractFanType::class, $contribution);
        $formPurchase->handleRequest($request);

        // DONATION SUBMIT
        if($form->isSubmitted() && $form->isValid()) {

            $cart = new XCart();

            foreach($contribution->getPurchases() as $purchase) {
                $contribution->removePurchase($purchase);
            }

            $contribution->setAmount($form['amount']->getData());
            $contribution->setIsDonation(true);

            $cart->addContract($contribution);
            $cart->setConfirmed(true);
            $cart->generateBarCode();

            $contribution->setCart($cart);

            $em->persist($cart);
            $em->persist($contribution);
            $em->flush();

            return $this->redirectToRoute('x_payment_checkout', ['code' => $cart->getBarcodeText()]);
        }

        // PURCHASE SUBMIT
        if($formPurchase->isSubmitted() && $formPurchase->isValid()) {

            $cart = new XCart();

            foreach($contribution->getPurchases() as $purchase) {
                if($purchase->getQuantity() == 0) {
                    $contribution->removePurchase($purchase);
                } else {
                    $purchase->setContractFan($contribution);
                    $em->persist($purchase);
                }
            }

            $contribution->initAmount();

            $cart->addContract($contribution);
            $cart->setConfirmed(true);
            $cart->generateBarCode();

            $contribution->setCart($cart);

            $em->persist($cart);
            $em->persist($contribution);
            $em->flush();

            return $this->redirectToRoute('x_payment_checkout', ['code' => $cart->getBarcodeText()]);
        }

        return $this->render('@X/XPublic/project.html.twig', array(
            'form' => $form->createView(),
            'form_purchase' => $formPurchase->createView(),
            'project' => $project,
            'has_products' => $hasProducts,
        ));
    }

    /**
     * @Route("/order/{code}", name="x_order")
     */
    public function orderAction(EntityManagerInterface $em, TicketingManager $ticketingManager, $code) {

        $cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code, 'paid' => true]);

        // Send tickets if not done when payment is succesful
        /*foreach($cart->getContracts() as $cf) {
            if(!$cf->getTicketsSent())
                $ticketingManager->generateAndSendYBTickets($cf);
        }*/
        
        $cart->setFinalized(true);
        $em->flush();

        return $this->render('XBundle:XPublic:order.html.twig', [
            'cart' => $cart,
        ]);
    }


    /**
     * @Route("/terms", name="x_terms")
     */
    public function termsAction()
    {
        return $this->render('@X/XPublic/terms.html.twig', []);
    }



    ///////////////////////// PAYMENT /////////////////////////

    /**
	 * @Route("/payment/{code}", name="x_payment_checkout")
	 */
	public function paymentCheckoutAction(EntityManagerInterface $em, Request $request, $code){
		
		$cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);

        /** @var XCart $cart */
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->isPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }
        
		if($request->getMethod() == 'POST' /*&& isset($_POST['accept_conditions']) && $_POST['accept_conditions']*/) {

            $amount = intval($_POST['amount']);

            if($cart->getOrder() == null) {
                $firstName = $_POST['first_name'];
                $lastName = $_POST['last_name'];
                $email = $_POST['email'];

                $order = new XOrder();
                $order->setEmail($email)
                       ->setFirstName($firstName)
                       ->setLastName($lastName)
                       ->setCart($cart);
            } else {
                $order = $cart->getOrder();
            }

            // check error
            /*/foreach($cart->getContracts() as $contribution) {
                $project = $contribution->getProject();
                // check if project validated
                foreach($contribution->getPurchases() as $purchase) {
                }
            }*/

			$em->persist($order);
			$em->flush();
		
            \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
            
            $source = $_POST['stripeSource'];
            
            try{
                $payment = new XPayment();
                $payment->setCart($cart)
                        ->setDate(new \DateTime())
                        ->setRefunded(false)
                        ->setAmount($cart->getAmount());
                
                $charge = \Stripe\Charge::create(array(
                    "amount" => $amount,
                    "currency" => "eur",
                    "source" => $source,
                    "description" => "Chapots - payment " . $cart->getId(),
                ));
                    
                $payment->setChargeId($charge->id);
                $em->persist($payment);

                $em->persist($cart);
                return $this->redirectToRoute('x_payment_success', array('code' => $cart->getBarcodeText())); //, 'sponsorship' => $sponsorship));
                
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
        }
        
		return $this->render('@X/XPublic/Payment/payment_checkout.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions']
        ));
    }
    

    /**
	 * @Route("/payment/pending/{code}", name="x_payment_pending")
	 */
	public function paymentPendingAction(EntityManagerInterface $em, Request $request, $code)
    {
        /** @var XCart $cart */
		$cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);
        
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->isPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        $source = $request->get('source');
        $client_secret = $request->get('client_secret');

        return $this->render('@X/XPublic/Payment/payment_pending.html.twig', array(
        	'cart' => $cart,
            'source' => $source,
            'client_secret' => $client_secret,
        ));
    }


	/**
	 * @Route("/payment/success/{code}", name="x_payment_success")
	 */
	public function paymentSuccessAction(EntityManagerInterface $em, Request $request, MailDispatcher $mailDispatcher, TicketingManager $ticketingManager, $code) {

        /** @var XCart $cart */
        $cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->isPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        // Send order recap
        $mailDispatcher->sendXOrderRecap($cart);

        foreach($cart->getContracts() as $contribution) {
            /** @var Project $project */
            $project = $contribution->getProject();

            $project->addAmount($contribution->getAmount());

            if (!$contribution->getIsDonation()) {
                foreach($contribution->getPurchases() as $purchase) {
                    $product = $purchase->getProduct();
                    $product->updateProductsSold($purchase->getQuantity());
                    $em->persist($product);
                }
            }

            // Check if threshold is reached
            if ($project->hasThreshold() && $project->getCollectedAmount() >= $project->getThreshold() && !$project->getNotifSuccessSent()) {
                $project->setSuccessful(true);
                $mailDispatcher->sendProjectThresholdConfirmed($project);
                $project->setNotifSuccessSent(true);
            }

            // Need to also send tickets
            if($project->isEvent() && $project->getSuccessful() && !$project->isPassed()) {
                if(!empty($contribution->getTicketsPurchases())) {
                    $ticketingManager->generateAndSendXTickets($contribution);
                }
            }

            $em->persist($project);
        }

        $cart->setPaid(true);
        $em->persist($cart);
        $em->flush();

        $this->addFlash('x_notice', 'Paiement bien reçu ! Vous devriez avoir reçu un récapitulatif par e-mail.');
        return $this->redirectToRoute('x_order', ['code' => $cart->getBarcodeText()]);

    }


    ///////////////////////// CONNEXION / DECONNEXION /////////////////////////

    /**
     * @Route("/signin", name="x_login")
     */
    public function loginAction(Request $request, CsrfTokenManagerInterface $tokenManager = null, UserInterface $user = null)
    {
        // à changer pcq tenir aussi compte contributeur!
        if($user != null) {
            if($user->isSuperAdmin() || $user->isArtistOwner()) {
                return $this->redirectToRoute('x_artist_dashboard');
            } else {
                return $this->redirectToRoute('x_homepage');
            }
        }

        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

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

        return $this->render('@X/XPublic/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }


    /**
     * @Route("/signout", name="x_logout")
     */
    public function logoutAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $tokenStorage->setToken(null);
        $session = $request->getSession();
        $session->invalidate();
        $response = new RedirectResponse($this->generateUrl('x_homepage'));
        $cookieNames = [
            $this->getParameter('session_name'),
            $this->getParameter('remember_me_name'),
        ];
        foreach ($cookieNames as $cookieName) {
            $response->headers->clearCookie($cookieName);
        }
        //$this->addFlash('x_notice', "Vous êtes bien déconnecté.");
        return $response;
    }


    ///////////////////////// AJAX /////////////////////////
	
	/**
	 * @Route("/api/submit-order-coordinates", name="x_ajax_post_order")
	 */
    public function orderAjaxAction(EntityManagerInterface $em, Request $request)
    {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $code = $_POST['cart_code'];

        /** @var XCart $cart */
        $cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);
        
        if ($cart == null || count($cart->getContracts()) == 0 || $cart->getPaid() || $cart->isRefunded()) {
            throw $this->createNotFoundException("Pas de panier, pas de paiement !");
        }

        $order = new XOrder();
        $order->setCart($cart)
               ->setEmail($email)
               ->setFirstName($firstName)
               ->setLastName($lastName);
        $cart->setOrder($order);

        $em->persist($order);
        $em->persist($cart);
        $em->flush();

        return new Response(' ', 200);
    }




}
