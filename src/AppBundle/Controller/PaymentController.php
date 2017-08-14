<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class PaymentController extends Controller
{
    /**
     * @Route("/cart/payment", name="fan_cart_payment_stripe")
     */
    public function fanCartAction(UserInterface $fan, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null || count($cart->getContracts()) == 0) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }

        if ($request->getMethod() == 'POST' && $_POST['accept_conditions']) {

            // TODO
            if($cart->isProblematic()) {
                return $this->createAccessDeniedException("Panier problématique");
            }

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey("sk_test_b75odA2dm9Og4grQZyFdn9HP");

            // Token is created using Stripe.js or Checkout!
            // Get the payment token submitted by the form:
            $token = $_POST['stripeToken'];

            // Charge the user's card:


            try {

                $stripe_customer = \Stripe\Customer::create(array(
                    "description" => "Customer for " . $fan->getEMail(),
                    "source" => $token
                ));

                $fan->setStripeCustomerId($stripe_customer->id);

                $charges = array();
                $payments = array();
                foreach($cart->getContracts() as $key => $contract) {
                    $charges[$key] = \Stripe\Charge::create(array(
                        // TODO assurer que cet amount ne peut pas être changé au cours du processus, par ex. avec un hach
                        "amount" => $contract->getAmount() * 100,
                        "currency" => "eur",
                        "description" => "Paiement du contrat numéro " . $contract->getId(),
                        "customer" => $stripe_customer->id
                    ));

                    $contract_artist = $contract->getContractArtist();

                    $payments[$key] = new Payment();
                    $payments[$key]->setChargeId($charges[$key]->id)->setDate(new \DateTime())->setUser($fan)
                        ->setContractFan($contract)->setContractArtist($contract_artist)->setRefunded(false)->setAmount($contract->getAmount());

                    $contract_artist->addAmount($contract->getAmount());

                    $em->persist($payments[$key]);
                    $em->flush();
                }

                $cart->setConfirmed(true)->setPaid(true);
                $em->persist($cart);

                $em->flush();

                // TODO
                // Si c'est le x-ième panier du fan dans les 24h, nous envoyer une notif : ça pourrait être une fraude
                // + lui envoyer un mail automatique (ou manuel)
                // + nous permettre d'annuler dans l'espace d'admin un paiement

                return $this->redirectToRoute('fan_cart_payment_stripe_success', array());

            } catch(\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];

                print('Status is:' . $e->getHttpStatus() . "\n");
                print('Type is:' . $err['type'] . "\n");
                print('Code is:' . $err['code'] . "\n");
                // param is '' in this case
                print('Param is:' . $err['param'] . "\n");
                print('Message is:' . $err['message'] . "\n");
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
            }
        }

        return $this->render('@App/Fan/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ));
    }

    /**
     * @Route("/cart/payment/success", name="fan_cart_payment_stripe_success")
     */
    public function fanCartSuccessAction(UserInterface $fan, Request $request) {
        $this->addFlash('notice', 'Paiement reçu');
        return $this->redirectToRoute('fan_cart');
    }


    /*
    private function createPayment($order)
    {
        $instruction = $order->getPaymentInstruction();
        $pendingTransaction = $instruction->getPendingTransaction();

        if ($pendingTransaction !== null) {
            return $pendingTransaction->getPayment();
        }

        $ppc = $this->get('payment.plugin_controller');
        $amount = $instruction->getAmount() - $instruction->getDepositedAmount();

        return $ppc->createPayment($instruction->getId(), $amount);
    }

    /**
    * @Route("/cart/payment", name="fan_cart_payment_stripe")
    *//*
    public function fanCartAction(UserInterface $fan, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        $form = $this->createForm(ChoosePaymentMethodType::class, null, [
            'amount' => $cart->getAmount(),
            'currency' => 'EUR',
            'predefined_data' => array(
                'stripe_checkout' => array(
                    'description' => 'My product',
                ),
            ),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ppc = $this->get('payment.plugin_controller');
            $ppc->createPaymentInstruction($instruction = $form->getData());

            $cart->setPaymentInstruction($instruction);

            $em->persist($cart);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_create', [
                'id' => $cart->getId(),
            ]));
        }

        return $this->render('@App/Fan/pay_cart_stripe.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/payment/create/{id}", name="payment_create")
     *//*
    public function paymentCreateAction(Cart $cart) {
        $payment = $this->createPayment($cart);

        $ppc = $this->get('payment.plugin_controller');
        $result = $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

        if ($result->getStatus() === Result::STATUS_SUCCESS) {
            return $this->redirect($this->generateUrl('app_orders_paymentcomplete', [
                'id' => $cart->getId(),
            ]));
        }

        throw $result->getPluginException();
    }*/
}
