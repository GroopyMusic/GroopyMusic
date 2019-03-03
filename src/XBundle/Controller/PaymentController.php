<?php

namespace XBundle\Controller;

use XBundle\Entity\XCart;
use XBundle\Entity\XPayment;
use XBundle\Entity\XOrder;
use XBundle\Entity\XPayUserInfo;

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

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentController extends Controller
{

	public function paymentAction(Request $request, $code){
		
		$em=$this->getDoctrine()->getManager();
		$x_cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);

		if($x_cart->getDonationAmount() == null){
			$product = $em->getRepository('XBundle:Product')->find($x_cart->getProduct());
			$is_donation = false;
			$amount = $product->getPrice() * $x_cart->getProductQuantity();
		}
		else{
			$is_donation = true;
			$amount = $x_cart->getDonationAmount();
		}
		

		if($request->getMethod() == 'POST' && isset($_POST['accept_conditions'])){
			$user_info = $em->getRepository('XBundle:XPayUserInfo')->findOneBy(array('cart' => $x_cart));

			$user_info->setCart($x_cart);


			$x_cart->setUserInfo($user_info);
			
			$em->persist($user_info);
			$em->persist($x_cart);
			$em->flush();
		
		\Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
		
		$source = $_POST['stripeSource'];
		
		try{
			$payment = new XPayment();
			$payment->setCart($x_cart)->setDate(new \DateTime())->setRefund(false)->setAmount($amount);
			
			$charge = \Stripe\Charge::create(array(
				"amount" => $amount * 100,
				"currency" => "eur",
				"source" => $source,
				"description" => "Donation"
				));
				
			$payment->setChargeId($charge->id);
			$em->persist($payment);

			
			return $this->redirectToRoute('x_payment_success', array('code' => $x_cart->getBarcodeText())); //, 'sponsorship' => $sponsorship));
			
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
		if($x_cart->getDonationAmount() == null){

			return $this->render('XBundle:Payment:payment.html.twig', array(
				'cart' => $x_cart,
				'amount' => $amount,
				'is_donation' => $is_donation,
				'product' => $product));
		}
		else{
			return $this->render('XBundle:Payment:payment.html.twig', array(
				'cart' => $x_cart,
				'amount' => $amount,
				'is_donation' => $is_donation));
		}
	}

	public function paymentSuccessAction(Request $request, $code) {

		$em=$this->getDoctrine()->getManager();
        /** @var Cart $cart */
        $cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);
        $project = $em->getRepository('XBundle:Projects')->find($cart->getProjects());
        $payment = $em->getRepository('XBundle:XPayment')->findOneBy(['cart' => $cart->getId()]);
        $project->setTotalAmount($project->getTotalAmount() + $payment->getAmount());
        $cart->setPaid(true);
        $em->persist($project);
        $em->flush();

        $this->addFlash('notice', 'Paiement bien reçu ! Votre commande est validée. Vous devriez avoir reçu un récapitulatif par e-mail.');

        return $this->render('XBundle:Payment:payment_success.html.twig');
    }

	public function paymentPendingAction(Request $request, $code)
    {
    	
		$em = $this->getDoctrine()->getManager();
		$cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);
        $source = $request->get('source');
        $client_secret = $request->get('client_secret');

        if($cart->getDonationAmount() == null){
			$product = $em->getRepository('XBundle:Product')->find($cart->getProduct());
			$amount = $product->getPrice() * $cart->getProductQuantity();
		}
		else{
			$amount = $cart->getDonationAmount();
		}

        return $this->render('XBundle:Payment:payment_pending.html.twig', array(
        	'cart' => $cart,
            'source' => $source,
            'client_secret' => $client_secret,
            'amount' => $amount
        ));
    }

    public function orderAjaxAction(Request $request, $code) {
    	$em = $this->getDoctrine()->getManager();
		$cart = $em->getRepository('XBundle:XCart')->findOneBy(['barcode_text' => $code]);

        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
		

        $user_info = new XPayUserInfo();
        $user_info->setCart($cart)->setEmail($email)->setFirstName($first_name)->setLastName($last_name);



        $em->persist($user_info);
        $em->flush();

        return new Response(' ', 200);
    }
}
