<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Services\MailDispatcher;
use Spipu\Html2Pdf\Html2Pdf;
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

        /** @var ContractFan $contract */
        $contract = $cart->getFirst();
        $contract_artist = $contract->getContractArtist();

        if ($request->getMethod() == 'POST' && $_POST['accept_conditions']) {

            $amount = intval($_POST['amount']);
            $fancontract_id = intval($_POST['fancontract_id']);

            // We set an explicit test for amount changes as it has legal impacts
            if($amount != $contract->getAmount() * 100 || $fancontract_id != $contract->getId()) {
                $this->addFlash('error', 'Vous avez modifié votre commande en cours de route ; merci de recommencer.');
                return $this->render('@App/User/pay_cart.html.twig', array(
                    'cart' => $cart,
                    'error_conditions' => false,
                    'contract_fan' => $contract,
                ));
            }

            if($contract_artist->isUncrowdable()) {
                $this->addFlash('error', "Il n'est plus possible de contribuer à cet événement.");
                return $this->redirectToRoute('artist_contract', ['id' => $contract->getId()]);
            }

            if ($cart->isProblematic()) {
                $this->addFlash('error', 'Votre panier contenait des articles expirés ; nous nous chargeons de le remettre à jour');

                $application = new Application($kernel);
                $application->setAutoExit(false);
                $input = new ArrayInput(array(
                    'command' => 'infos:carts:problematic',
                ));
                $output = new NullOutput();
                $application->run($input, $output);

                return $this->redirectToRoute('artist_contract', ['id' => $contract->getId()]);
            }

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

            // Token is created using Stripe.js or Checkout!
            // Get the payment token submitted by the form:
            $source = $_POST['stripeSource'];

            // Charge the user's card:
            try {
                $payment = new Payment();
                $payment->setDate(new \DateTime())->setUser($user)
                    ->setContractFan($contract)->setContractArtist($contract_artist)->setRefunded(false)->setAmount($contract->getAmount());

                $contract_artist->addAmount($contract->getAmount());

                if ($contract_artist instanceof ContractArtist) {
                    $contract_artist->addTicketsSold($contract->getCounterPartsQuantity());
                }

                $charge = \Stripe\Charge::create(array(
                    "amount" => $amount,
                    "currency" => "eur",
                    "description" => "Paiement du contrat numéro " . $contract->getId(),
                    "source" => $source,
                ));

                $payment->setChargeId($charge->id);
                $em->persist($payment);

                $cart->setConfirmed(true)->setPaid(true);
                $em->persist($cart);

                $em->flush();

                return $this->redirectToRoute('user_cart_payment_stripe_success', array('id' => $contract->getId()));

            } catch (\Stripe\Error\Card $e) {
                $this->addFlash('error', 'Une erreur est survenue avec votre carte, veuillez réessayer');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            } catch (\Stripe\Error\RateLimit $e) {
                $this->addFlash('error', 'Trop de requêtes simultanées, veuillez réessayer');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            } catch (\Stripe\Error\InvalidRequest $e) {
                $this->addFlash('error', 'Paramètres invalides, veuillez réessayer');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            } catch (\Stripe\Error\Authentication $e) {
                $this->addFlash('error', "L'authentification Stripe a échoué, veuillez réessayer");
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            } catch (\Stripe\Error\ApiConnection $e) {
                $this->addFlash('error', 'Une erreur est survenue avec le système de paiement, veuillez réessayer');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            } catch (\Stripe\Error\Base $e) {
                $this->addFlash('error', 'Une erreur est survenue avec le système de paiement, veuillez réessayer (nous avons alerté les administrateurs de cette erreur, ils s\'en occupent au plus tôt');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            }
            catch(\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue');
                $this->get(MailDispatcher::class)->sendAdminStripeError($e, $user, $cart);
            }
        }

        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => isset($_POST['accept_conditions']) && !$_POST['accept_conditions'],
            'contract_fan' => $contract,
        ));
    }

    /**
     * @Route("/cart/payment/success/{id}", name="user_cart_payment_stripe_success")
     */
    public function cartSuccessAction(ContractFan $cf) {
        $this->addFlash('notice', 'Votre paiement a bien été reçu. Vous allez recevoir un récapitulatif par e-mail. Vos tickets vous seront envoyés si l\'artiste ' . $cf->getContractArtist()->getArtist()->getArtistname() . ' parvient à débloquer ce concert.');

        $cf->generateBarCode();
        $order_html = $this->get('twig')->render('AppBundle:PDF:order.html.twig', array('cf' => $cf));
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($order_html);
        $html2pdf->output($cf->getPdfPath(), 'F');

        $this->get(MailDispatcher::class)->sendOrderRecap($cf);

        $em = $this->getDoctrine()->getManager();
        $em->persist($cf);
        $em->flush();

        return $this->redirectToRoute('user_paid_carts');
    }

    /**
     * @Route("/cart/payment/pending", name="user_cart_payment_pending")
     */
    public function cartPendingAction() {
        return $this->render('@App/User/threeds_pending.html.twig');
    }
}
