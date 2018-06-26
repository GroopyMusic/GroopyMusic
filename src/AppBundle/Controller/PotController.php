<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtistPot;
use AppBundle\Entity\ContractFan;
use AppBundle\Form\ContractFanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PotController extends Controller
{
    // Duplicated from User&PublicController
    private function createCartForUser($user)
    {
        $cart = new Cart();
        $cart->setUser($user);
        $this->getDoctrine()->getManager()->persist($cart);
        return $cart;
    }

    private function cleanCart(Cart $cart, $em)
    {
        if ($cart->getPaid() && $cart->getConfirmed()) {
            return $this->createCartForUser($cart->getUser());
        } else {
            foreach ($cart->getContracts() as $contract) {
                $cart->removeContract($contract);
                $em->remove($contract);
            }
            return $cart;
        }
    }

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/", name="pot_index")
     */
    public function indexAction(EntityManagerInterface $em)
    {
        $pots = $em->getRepository('AppBundle:ContractArtistPot')->findVisible();

        return $this->render('@App/Pot/index.html.twig', [
            'pots' => $pots,
        ]);
    }

    /**
     * @Route("/{id}-{slug}", name="pot_pot")
     */
    public function potAction(ContractArtistPot $contract, $slug = null, EntityManagerInterface $em, Request $request, UserInterface $user)
    {
        if ($contract->getArtist()->getSlug() != $slug) {
            return $this->redirectToRoute('pot_index', ['id' => $contract->getId(), 'slug' => $contract->getArtist()->getSlug()]);
        }

        $cf = new ContractFan($contract);
        $form = $this->createForm(ContractFanType::class, $cf, ['entity_manager' => $em]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($contract->isUncrowdable()) {
                $this->addFlash('error', 'errors.sales.uncrowdable'); // TODO
            } elseif ($cf->getCounterPartsQuantityOrganic() > $contract->getTotalNbAvailable()) {
                $this->addFlash('error', 'errors.order_max');
            } else {
                /** @var Cart $cart */
                if ($user != null) {
                    $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
                }

                if (!isset($cart) || $cart == null) {
                    $cart = $this->createCartForUser($user);
                } else {
                    $cart = $this->cleanCart($cart, $em);
                }

                foreach ($cf->getPurchases() as $purchase) {
                    if ($purchase->getQuantity() == 0) {
                        $cf->removePurchase($purchase);
                    }
                }
                $cart->addContract($cf);

                $em->flush();
                $request->getSession()->set('cart_id', $cart->getId());
                return $this->redirectToRoute('checkout');
            }
        }

        return $this->render('@App/Pot/pot.html.twig', [
            'contract' => $contract,
            'form' => $form->createView(),
        ]);
    }
}
