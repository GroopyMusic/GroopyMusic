<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Services\MailDispatcher;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class PaymentController extends Controller
{
    /**
     * @Route("/cart/3DS/payment", name="user_cart_3DS_payment_stripe")
     */
    public function ThreeDSAction($source, $user) {



    }

    /**
     * @Route("/cart/payment", name="user_cart_payment_stripe")
     */
    public function cartAction(Request $request,UserInterface $user)
    {
        $kernel = $this->get('kernel');

        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        /** @var Cart $cart */

        if ($cart == null || count($cart->getContracts()) == 0) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }

        if ($request->getMethod() == 'POST' && $_POST['accept_conditions']) {

            if ($cart->isProblematic()) {
                $this->addFlash('error', 'Votre panier contenait des articles expirés ; nous nous chargeons de le remettre à jour');

                $application = new Application($kernel);
                $application->setAutoExit(false);
                $input = new ArrayInput(array(
                    'command' => 'infos:carts:problematic',
                ));
                $output = new NullOutput();
                $application->run($input, $output);

                return $this->redirectToRoute('homepage');
            }

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey("sk_test_b75odA2dm9Og4grQZyFdn9HP");

            // Token is created using Stripe.js or Checkout!
            // Get the payment token submitted by the form:
            $source = $_POST['stripeSource'];
            $source_object = \Stripe\Source::retrieve($source);

            $source_type = $source_object['type'];
            $limit = $source_type == 'bancontact' ? 1 : 1000;

            // Charge the user's card:
            try {
                try {
                    if ($user->getStripeCustomerId() != null) {
                        throw new \Exception();
                        //$stripe_customer = \Stripe\Customer::retrieve($user->getStripeCustomerId());
                        //$stripe_customer->source = $source;
                    } else {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    $stripe_customer = \Stripe\Customer::create(array(
                        "description" => "Customer for " . $user->getEmail(),
                        "source" => $source,
                    ));

                    $user->setStripeCustomerId($stripe_customer->id);
                }

                $charges = array();
                $payments = array();
                $i = 0;

                foreach ($cart->getContracts() as $key => $contract) {

                    $i++;
                    if ($i <= $limit) {
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

                        if ($contract_artist instanceof ContractArtist) {
                            $contract_artist->addTicketsSold($contract->getCounterPartsQuantity());
                        }

                        $em->persist($payments[$key]);
                        $em->flush();
                    }
                }

                $cart->setConfirmed(true)->setPaid(true);
                $em->persist($cart);

                $em->flush();

                return $this->redirectToRoute('user_cart_payment_stripe_success', array());

            } catch (\Stripe\Error\Card $e) {
                $this->addFlash('error', 'Une erreur est survenue avec votre carte, veuillez réessayer');
            } catch (\Stripe\Error\RateLimit $e) {
                $this->addFlash('error', 'Trop de requêtes simultanées, veuillez réessayer');
            } catch (\Stripe\Error\InvalidRequest $e) {
                $this->addFlash('error', 'Paramètres invalides, veuillez réessayer');
            } catch (\Stripe\Error\Authentication $e) {
                $this->addFlash('error', "L'authentification Stripe a échoué, veuillez réessayer");
            } catch (\Stripe\Error\ApiConnection $e) {
                $this->addFlash('error', 'Une erreur est survenue avec le système de paiement, veuillez réessayer');
            } catch (\Stripe\Error\Base $e) {
                $this->addFlash('error', 'Une erreur est survenue avec le système de paiement, veuillez réessayer (nous avons alerté les administrateurs de cette erreur, ils s\'en occupent au plus tôt');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
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
    public function cartSuccessAction() {
        $this->addFlash('notice', 'Paiement reçu');
        return $this->redirectToRoute('user_cart');
    }
}
