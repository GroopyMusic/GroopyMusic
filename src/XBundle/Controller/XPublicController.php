<?php

namespace XBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class XPublicController extends Controller
{
    /**
     * @Route("/", name="x_homepage")
     */
    public function indexAction()
    {
        return $this->render('XBundle:XPublic:index.html.twig');
    }


    /**
     * @Route("/signin", name="x_login")
     */
    public function loginAction(Request $request, CsrfTokenManagerInterface $tokenManager = null, UserInterface $user = null)
    {
        //$file = 'test.txt';
        //file_put_contents($file, $request);
        
        return $this->render('XBundle:XPublic:login.html.twig');
    }


    /**
     * @Route("/signout", name="x_logout")
     */
    public function logoutAction()
    {

    }
}
