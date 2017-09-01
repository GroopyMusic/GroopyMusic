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
//Uses for the form (suggestionBox)
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

    /**
     * @Route("/suggestions", name="suggestionBox")
     */
    public function suggestionBoxAction(Request $request, UserInterface $user = null){

        $em = $this->getDoctrine()->getManager();

        $suggestionBox = new SuggestionBox();

        if($user != null){
            $suggestionBox->setName($user->getLastname());
            $suggestionBox->setFirstname($user->getFirstname());
            $suggestionBox->setEmail($user->getEmail());
            $form = $this->createForm(UserSuggestionBoxType::class, $suggestionBox);
        }else{
            $form = $this->createForm(SuggestionBoxType::class, $suggestionBox);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($suggestionBox);
            $em->flush();

            $this->addFlash('notice', 'Suggestion bien envoyée. Merci !');

            if($suggestionBox->getMailCopy()) {
                $recipient = $suggestionBox->getEmail();
                $recipientName = $suggestionBox->getDisplayName();

                // TODO envoi de l'e-mail
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('AppBundle:Public:suggestionBox.html.twig', array('form' => $form->createView(),));
    }
}