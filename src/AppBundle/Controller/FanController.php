<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class FanController extends Controller
{
    /**
     * @Route("/home", name="fan_home")
     */
    public function homeAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();

        $currentContracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();

        return $this->render('@App/Fan/fan_home.html.twig', array(
            'currentContracts' => $currentContracts,
        ));

    }
}
