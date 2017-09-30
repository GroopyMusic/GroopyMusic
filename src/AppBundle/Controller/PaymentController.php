<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class PaymentController extends Controller
{
    /**
     * @Route("/cart/payment", name="user_cart_payment_stripe")
     */
    public function cartAction(UserInterface $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

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
                    "description" => "Customer for " . $user->getEMail(),
                    "source" => $token
                ));

                $user->setStripeCustomerId($stripe_customer->id);

                $charges = array();
                $payments = array();

                foreach($cart->getContracts() as $key => $contract) {
                    /** @var ContractFan $contract */
                    $charges[$key] = \Stripe\Charge::create(array(
                        // TODO assurer que cet amount ne peut pas être changé au cours du processus, par ex. avec un hach
                        "amount" => $contract->getAmount() * 100,
                        "currency" => "eur",
                        "description" => "Paiement du contrat numéro " . $contract->getId(),
                        "customer" => $stripe_customer->id
                    ));

                    $contract_artist = $contract->getContractArtist();

                    $payments[$key] = new Payment();
                    $payments[$key]->setChargeId($charges[$key]->id)->setDate(new \DateTime())->setUser($user)
                        ->setContractFan($contract)->setContractArtist($contract_artist)->setRefunded(false)->setAmount($contract->getAmount());

                    $contract_artist->addAmount($contract->getAmount());

                    if($contract_artist instanceof ContractArtist) {
                        $contract_artist->addTicketsSold($contract->getCounterPartsQuantity());
                    }

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

        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
        ));
    }

    /**
     * @Route("/cart/payment/success", name="user_cart_payment_stripe_success")
     */
    public function cartSuccessAction(UserInterface $user, Request $request) {
        $this->addFlash('notice', 'Paiement reçu');
        return $this->redirectToRoute('user_cart');
    }
}
