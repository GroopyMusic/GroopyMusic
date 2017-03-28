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
// uses du formulaire
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
     * @Route("/", name="suggestionBox")
     */
    public function suggestionBoxAction(){

        $em = $this->getDoctrine()->getManager();

        // Création de la suggestion box
        $suggestionBox = new SuggestionBox();
        
        // Création du formbuilder -> formFactory
        $formbuilder = $this->get('form.factory')->createBuilder(FormType::class, $suggestionBox);

        // ajout des champs du formulaire
        $formbuilder
            ->add('date',       DateType::class)
            ->add('name',       TextType::class)
            ->add('firstName',  TextType::class)
            ->add('email',      TextType::class)
            ->add('object',     TextAreaType::class)
            ->add('message',    TextType::class)
            ->add('mailCopy',   CheckboxType::class)
            ->add('send',       SubmitType::class)
        ;

        //des tests doivent venir ici ? 
        // fin des tests

        // on génère le formulaire
        $form = $formbuilder->getForm();

        //on persiste et on flush
        // $em->persist($suggestionBox);
        // $em->flush();

        // /!\ j'en suis ici !
            // https://openclassrooms.com/courses/developpez-votre-site-web-avec-le-framework-symfony/creer-des-formulaires-avec-symfony#/id/r-3623367
        // /!\ ici la suite


        //petit message de bien envoyé
        $this->addFlash('notice', 'Message bien envoyé ! Merci !');

        return $this->render('AppBundle:Public:suggestionBox.html.twig', array('form' => $form->createView(),));
    }
}












