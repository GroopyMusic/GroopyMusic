<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Step;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ArtistController extends Controller
{
    /**
     * @Route("/home", name="artist_home")
     */
    public function homeAction(Request $request, UserInterface $artist)
    {
        $em = $this->getDoctrine()->getManager();
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);

        return $this->render('@App/Artist/artist_home.html.twig', array(
            'currentContract' => $currentContract,
        ));
    }

    /**
     * @Route("/steps", name="artist_steps")
     */
    public function stepsAction(UserInterface $artist) {

        $em = $this->getDoctrine()->getManager();
        $phases = $em->getRepository('AppBundle:Phase')->findAllWithSteps();
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);

        return $this->render('@App/Artist/steps.html.twig', array(
            'phases' => $phases,
            'currentContract' => $currentContract,
        ));
    }

    /**
     * @Route("/steps/new-contract-{id}", name="artist_new_contract")
     */
    public function contractAction(Step $step, UserInterface $artist, Request $request) {

        // Only unlocked phases are allowed
        $phase = $step->getPhase();
        if($phase->getNum() > $artist->getPhase()->getNum()) {
            throw $this->createAccessDeniedException("Ce palier appartient à une phase que vous n'avez pas encore débloquée.");
        }

        $em = $this->getDoctrine()->getManager();

        // Only one contract at a time
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);
        if($currentContract != null) {
            throw $this->createAccessDeniedException("Interdit de s'inscrire à deux paliers en même temps !");
        }

        // New contract creation
        $contract = new ContractArtist();
        $contract->setArtist($artist)->setStep($step);

        $th_date = new \DateTime;
        $th_date->modify('+ ' . $step->getDeadlineDuration() . ' days');
        $contract->setTheoriticalDeadline($th_date);

        $form = $this->createFormBuilder($contract);
        $form->add('accept_conditions', CheckboxType::class, array('required' => true))
             ->add('submit', SubmitType::class, array());
        $form = $form->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $deadline = new \DateTime();
            $deadline->modify('+ ' . $step->getDeadlineDuration() . ' days');
            $contract->setDate(new \DateTime());
            $contract->setDateEnd($deadline);

            // We re-check that there doesn't exist another contract for that artist before DB insertion
            $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);
            if($currentContract != null) {
                throw $this->createAccessDeniedException("Interdit de s'inscrire à deux paliers en même temps !");
            }

            $em->persist($contract);
            $em->flush();

            $this->addFlash('notice', 'Bien reçu');

            return $this->redirectToRoute('user_see_contract', ['id' => $contract->getId()]);
        }

        return $this->render('@App/Artist/new_contract.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }

    /**
     * @Route("/contracts", name="artist_contracts")
     */
    public function contractsAction(UserInterface $artist) {

        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findBy(array('artist' => $artist), array('dateEnd' => 'DESC'));

        return $this->render('@App/Artist/contracts.html.twig', array(
           'contracts' => $contracts,
        ));

    }

}
