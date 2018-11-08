<?php

namespace XBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PaymentController extends Controller
{
	public function paymentAction(Request $request){
		\Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));
		
		\Stripe\Charge::create(array(
			"amount" => 2000,
			"currency" => "eur",
			"source" => "tok_mastercard",
			"description" => "Donation"
			));
			
		return $this->render('XBundle:Payment:payment.html.twig');
	}
}