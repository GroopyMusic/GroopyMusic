<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
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
class PaymentController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/cart/payment", name="user_cart_payment_stripe")
     */
    public function cartAction(Request $request, UserInterface $user, RewardSpendingService $rewardSpendingService, SponsorshipService $sponsorshipService, LoggerInterface $logger)
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
        $artist = $contract_artist->getArtist();

        if ($request->getMethod() == 'POST' && $_POST['accept_conditions']) {

            $amount = intval($_POST['amount']);
            $fancontract_id = intval($_POST['fancontract_id']);

            // We set an explicit test for amount changes as it has legal impacts
            if (floatval($amount) !=  floatval($contract->getAmount() * 100) || $fancontract_id != $contract->getId()) {
                $this->addFlash('error', 'errors.order_changed');
                return $this->render('@App/User/pay_cart.html.twig', array(
                    'cart' => $cart,
                    'error_conditions' => false,
                    'contract_fan' => $contract,
                ));
            }

            if ($contract_artist->isUncrowdable()) {
                $this->addFlash('error', 'errors.event_uncrowdable');
                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), $artist->getSlug()]);
            } elseif ($contract->getCounterPartsQuantityOrganic() > $contract_artist->getTotalNbAvailable()) {
                $this->addFlash('error', 'errors.order_max');
                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), $artist->getSlug()]);
            } elseif ($contract->getCounterPartsQuantity() > $contract_artist->getTotalNbAvailable() + ContractArtist::MAXIMUM_PROMO_OVERFLOW) {
                $this->addFlash('error', 'errors.order_max_promo');
                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), $artist->getSlug()]);
            }

            if ($cart->isProblematic()) {
                $this->addFlash('error', 'errors.problematic_cart');

                $application = new Application($kernel);
                $application->setAutoExit(false);
                $input = new ArrayInput(array(
                    'command' => 'infos:carts:problematic',
                ));
                $output = new NullOutput();
                $application->run($input, $output);

                return $this->redirectToRoute('artist_contract', ['id' => $contract_artist->getId(), $artist->getSlug()]);
            }

            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

            // Token is created using Stripe.js or Checkout!
            // Get the payment token submitted by the form:
            $source = $_POST['stripeSource'];

            // Charge the user's card:
            try {
                $contract->calculatePromotions();

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
                    "description" => "Un-Mute - purchase " . $contract->getId(),
                    "source" => $source,
                ));

                $payment->setChargeId($charge->id);
                $em->persist($payment);

                $cart->setConfirmed(true)->setPaid(true);
                //reward
                $rewardSpendingService->consumeReward($contract);
                //sponsorship
                $sponsorship = $sponsorshipService->giveSponsorshipRewardOnPurchaseIfPossible($user, $contract_artist);
                $em->persist($cart);

                $em->flush();

                return $this->redirectToRoute('user_cart_payment_stripe_success', array('id' => $contract->getId(), 'sponsorship' => $sponsorship));

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
            'contract_fan' => $contract,
            'is_event_checkout' => $contract_artist instanceof ContractArtist,
            'is_sales_checkout' => $contract_artist instanceof Contra
        ));
    }

    /**
     * @Route("/cart/payment/success/{id}", name="user_cart_payment_stripe_success")
     */
    public function cartSuccessAction(Request $request, ContractFan $cf, TranslatorInterface $translator, PDFWriter $writer)
    {
        $this->addFlash('notice', $translator->trans('notices.payment', ['%artist%' => $cf->getContractArtist()->getArtist()->getArtistname()]));
        if ($request->get('sponsorship')) {
            $this->addFlash('notice', $translator->trans('notices.sponsorship.cart_success', []));
        }
        $writer->writeOrder($cf);

        $em = $this->getDoctrine()->getManager();

        $em->persist($cf);
        $em->flush();

        return $this->redirectToRoute('user_cart_send_order_recap', ['id' => $cf->getId(), 'is_payment' => true]);
    }

    /**
     * @Route("/cart/send-recap-{id}", name="user_cart_send_order_recap")
     */
    public function sendOrderRecap(Request $request, ContractFan $cf, MailDispatcher $dispatcher, TicketingManager $ticketingManager)
    {
        $dispatcher->sendOrderRecap($cf);

        if ($cf->getContractArtist() instanceof ContractArtist && $cf->getContractArtist()->getCounterPartsSent()) {
            $ticketingManager->sendUnSentTicketsForContractFan($cf);
        }

        return $this->redirectToRoute('user_paid_carts',['is_payment' => $request->get('is_payment')]);
    }

    /**
     * @Route("/cart/payment/pending/{contract_id}", name="user_cart_payment_pending")
     */
    public function cartPendingAction(Request $request, UserInterface $user, $contract_id)
    {
        $em = $this->getDoctrine()->getManager();

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        /** @var Cart $cart */
        if ($cart == null || count($cart->getContracts()) == 0) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }

        $contract = $em->getRepository('AppBundle:ContractFan')->find($contract_id);
        /** @var ContractFan $contract */
        $contract_cart = $cart->getFirst();
        if ($contract == null || $contract_cart == null || $contract_cart->getId() != $contract->getId() || $contract_cart->getAmount() != $contract->getAmount()) {
            $this->addFlash('error', 'errors.order_changed_counterparts');
            return $this->redirectToRoute('catalog_crowdfundings');
        }

        $source = $request->get('source');
        $client_secret = $request->get('client_secret');

        return $this->render('@App/User/threeds_pending.html.twig', array(
            'cart' => $cart,
            'source' => $source,
            'client_secret' => $client_secret,
            'contract_fan' => $contract,
        ));
    }
}
