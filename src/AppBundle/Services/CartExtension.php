<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartExtension extends \Twig_Extension
{
    private $em;
    private $token_storage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $token_storage)
    {
        $this->em = $em;
        $this->token_storage = $token_storage;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('cart_articles_nb', array($this, 'cart_articles_nb')),
        );
    }

    public function cart_articles_nb(User $user = null) {
        if($user == null)
            $user = $this->token_storage->getToken()->getUser();

        $cart = $this->em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if($cart == null) {
            return 0;
        }

        else {
            return $cart->getNbArticles();
        }
    }
}