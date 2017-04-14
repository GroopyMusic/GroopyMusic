<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class PublicController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Public:home.html.twig');
    }

    /**
     * @Route("/conditons", name="conditions")
     */
    public function conditionsAction() {
        return $this->render('AppBundle:Public:conditions.html.twig');
    }

    /**
     * @Route("/steps", name="steps")
     */
    public function stepsAction() {

         $em = $this->getDoctrine()->getManager();
         $phases = $em->getRepository('AppBundle:Phase')->findAllWithSteps();

        return $this->render('@App/Public/steps.html.twig', array(
            'phases' => $phases,
        ));
    }

    /**
     * @Route("/step-{id}", name="step")
     */
    public function stepAction(Step $step) {

        return $this->render('@App/Public/step.html.twig', array(
            'step' => $step,
        ));
    }
}
