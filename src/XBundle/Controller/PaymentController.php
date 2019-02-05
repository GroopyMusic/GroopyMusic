<?php

namespace XBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use XBundle\Entity\XOrder;

use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PDFWriter;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

class PaymentController extends Controller
{
	public function paymentAction(Request $request){
		
		$em=$this->getDoctrine()->getManager();
		if($request->getMethod() == 'POST' && isset($_POST['accept_conditon']) &$ $_POST['accept_conditon']){
			$amount = intval($_POST['amount']);
			

			$first_name = $_POST['first_name'];
			$last_name = $_POST['last_name'];
			$email = $_POST['email'];

			$order = new XOrder();
			$order->setEmail($email)->setFirstName($first_name)->setLastName($last_name);

			$errors = $validator->validate($order);
			if(count($errors) > 0) {
				$this->addFlash('error', 'errors.order_coords');
				return $this->render('XBundle:Payment:payment.html.twig'
				);
			}
			
			$em->persist($order);
			$em->flush();
		
		\Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
		
		$source = $_POST['stripeSource'];
		
		try{
		
			$payment = new Payment();
			$payment->setDate(new \DateTime())->setUser(null)
						->setRefunded(false)->setAmount($amount);
			
			$charge = \Stripe\Charge::create(array(
				"amount" => $amount,
				"currency" => "eur",
				"source" => $source,
				"description" => "Donation"
				));
				
			$payment->setChargeId($charge->id);
			$em->persist($payment);
			
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
		return $this->render('XBundle:Payment:payment.html.twig');
	}
	
	public function paymentPendingAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
        $source = $request->get('source');
        $client_secret = $request->get('client_secret');

        return $this->render('XBundle:Payment:payment_pending.html.twig', array(
            'source' => $source,
            'client_secret' => $client_secret,
        ));
    }

    public function orderAjaxAction(Request $request) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
		
		$em = $this->getDoctrine()->getManager();

        $order = new XOrder();
        $order->setEmail($email)->setFirstName($first_name)->setLastName($last_name);



        $em->persist($order);
        $em->flush();

        return new Response(' ', 200);
    }
}
