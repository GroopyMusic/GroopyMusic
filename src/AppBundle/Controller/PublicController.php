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
//
use AppBundle\Form\SuggestionBoxType;
use AppBundle\Form\UserSuggestionBoxType;

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
    public function suggestionBoxAction(Request $request, UserInterface $user=null){

        $em = $this->getDoctrine()->getManager();

        // Création de la suggestion box
        $suggestionBox = new SuggestionBox();

        if($user != null){
            $suggestionBox->setName($user->getLastName());
            $suggestionBox->setFirstName($user->getFirstName());
            $suggestionBox->setEmail($user->getEmail());
            $form = $this->createForm(UserSuggestionBoxType::class, $suggestionBox);
        }else{
            $form = $this->createForm(SuggestionBoxType::class, $suggestionBox);
        }
        
        if($request->isMethod('POST')){

            $form->handleRequest($request);

            if($form->isValid()){
                $em->persist($suggestionBox);
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'Suggestion bien envoyée. Merci !');
                return $this->render('AppBundle:Public:home.html.twig');
            }


        }

        return $this->render('AppBundle:Public:suggestionBox.html.twig', array('form' => $form->createView(),));
    }
}












