<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\VenueConfig;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Exception\YBAuthenticationException;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\UserRolesManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use XBundle\Exception\NoAuthenticationException;
use XBundle\Exception\NotAllowedException;

abstract class BaseController extends Controller
{
    protected $container;
    protected $logger;
    protected $mailDispatcher;
    protected $em;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, MailDispatcher $mailDispatcher, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
        $this->em = $em;
    }

    protected function assertOwns(UserInterface $user, Artist $artist) {
        $userRolesManager = $this->get(UserRolesManager::class);

        if(!$user->owns($artist) && !$userRolesManager->userHasRole($user, 'ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("You don't own this artist!");
        }
    }

    protected function suppressArtist(Artist $artist) {
        $artist->setDeleted(true);

        foreach($this->em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist]) as $o_request) {
            $this->em->remove($o_request);
        }

        $this->em->persist($artist);
    }

    ///////////////////////////////////////////////
    ///Private, checkout-specific methods//////////
    ///////////////////////////////////////////////
    protected function createCartForUser($user)
    {
        $cart = new Cart();
        $cart->setUser($user);
        $cart->generateBarCode();
        $this->getDoctrine()->getManager()->persist($cart);
        return $cart;
    }

    # Pupulates cart with empty orders, one for each given $artistContracts
    protected function populateCart(Cart $cart, $artistContracts) {
        foreach($artistContracts as $artistContract) {
            $fanContract = new ContractFan($artistContract);
            $cart->addContract($fanContract);
        }
        return $cart;
    }

    /**
     * Creates a new Cart filled with $cfs
     */
    protected function handleCheckout($cfs, $user, Request $request) {
        /** @var Cart $cart */
        $cart = null;

        $cart = $this->createCartForUser($user);

        foreach($cfs as $cf) {
            /** @var ContractFan $cf */
            $qty = 0;
            foreach ($cf->getPurchases() as $purchase) {
                $pqty = $purchase->getQuantity();
                if ($pqty == 0 || $pqty == null) {
                    $cf->removePurchase($purchase);
                }
                $qty += $pqty;
            }
            if($qty == 0) {
                if ($cart->hasContract($cf)) {
                    $cart->removeContract($cf);
                }
            }
            else {
                if(!$cart->hasContract($cf)) {
                    $cart->addContract($cf);
                }
            }
        }

        $this->em->persist($cart);
        $this->em->flush();
        return $cart;
    }

    ///////////////////////////////////////////////
    ///YB                                //////////
    ///////////////////////////////////////////////
    protected function checkIfAuthorized($user, YBContractArtist $campaign = null, $mustBeSuperAdmin = false) {
        if(!$user || !$user instanceof User) {
            throw new YBAuthenticationException();
        }
        if($campaign != null && (!$user->ownsYBCampaign($campaign) && !$user->isSuperAdmin())) {
            throw new YBAuthenticationException();
        }
        if($mustBeSuperAdmin && !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
    }

    protected function checkIfAuthorizedVenue($user, Venue $venue = null){
        if(!$user || !$user instanceof User) {
            throw new YBAuthenticationException();
        }
        if($venue != null && !$user->ownsYBVenue($venue)) {
            throw new YBAuthenticationException();
        }
    }

    protected function checkIfAuthorizedVenueConfig($user, VenueConfig $config = null){
        if(!$user || !$user instanceof User) {
            throw new YBAuthenticationException();
        }
        if($config != null && !$user->ownsYBVenue($config->getVenue()) && !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
    }

    protected function checkIfAuthorizedVenueBlock($user, Block $blk = null){
        if(!$user || !$user instanceof User) {
            throw new YBAuthenticationException();
        }
        if($blk != null && !$user->ownsYBVenue($blk->getConfig()->getVenue()) && !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
    }

    protected function checkCampaignCode(YBContractArtist $campaign, $code) {
        if($campaign->getCode() != $code) {
            throw $this->createAccessDeniedException();
        }
    }



    ///////////////////////////////////////////////
    /// X - CHAPOTS                      //////////
    ///////////////////////////////////////////////
    protected function checkIfArtistAuthorized($user, $project = null) {
        if (!$user || !$user instanceof User) {
            throw new NoAuthenticationException();
        }
        if (!$user->isSuperAdmin() && !$user->isArtistOwner()) {
            throw new NotAllowedException();
        }
        if ($project != null && (!$user->ownsProject($project) && !$user->isSuperAdmin())){
            throw new NotAllowedException();
        }
    }



}