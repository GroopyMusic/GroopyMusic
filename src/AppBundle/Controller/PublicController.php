<?php
// src/AppBundle/Controller/PublicController.php

namespace AppBundle\Controller;

use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use AppBundle\Entity\SuggestionBox;
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

    /**
     * @Route("/", name="boite-a-idees")
     */
    public function suggestionBoxAction(){

        $em = $this->getDoctrine()->getManager();

        // Création de la suggestion box
        $suggestionBox = new SuggestionBox();
        //on ajoute toutes les données dans l'entité
        $suggestionBox->setName("d'Oultremont");
        $suggestionBox->setFirstname("Matthieu");
        $suggestionBox->setEmail("matthieu.doultremont@gmail.com");
        $suggestionBox->setObject("Optimisation bien cool !");
        $suggestionBox->setMessage("Ajoute un HelloWorld ! C'est de la balle.");
        $suggestionBox->setMailCopy(true);

        //des tests doivent venir ici ? 

        // fin des tests

        //on persiste et on flush
        $em->persist($suggestionBox);
        $em->flush();

        //petit message de bien envoyé
        $this->addFlash('notice', 'Message bien envoyé ! <br>Merci !');

        return $this->render('AppBundle:Public:suggestionBox.html.twig');
    }
}












