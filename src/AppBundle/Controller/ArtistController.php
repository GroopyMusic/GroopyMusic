<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Step;
use AppBundle\Form\ArtistType;
use AppBundle\Form\ContractArtistType;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Artist;

class ArtistController extends Controller
{
    /**
     * @Route("/home", name="artist_home")
     */
    public function homeAction(Request $request, UserInterface $user, Artist $artist)
    {
        $em = $this->getDoctrine()->getManager();
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);

        return $this->render('@App/Artist/artist_home.html.twig', array(
            'currentContract' => $currentContract,
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/edit-profile", name="artist_profile_edit")
     */
    public function editProfileAction(Request $request, UserInterface $user, Artist $artist) {

        $form = $this->createForm(ArtistType::class, $artist);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($artist);
            $em->flush();

            $this->addFlash('notice', 'Bien reçu');
            return $this->redirectToRoute('artist_profile_edit', ['id' => $artist->getId()]);
        }

        return $this->render('@App/Artist/edit_profile.html.twig', array(
            'artist' => $artist,
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/steps", name="artist_steps")
     */
    public function stepsAction(UserInterface $user, Artist $artist) {

        $em = $this->getDoctrine()->getManager();
        $phases = $em->getRepository('AppBundle:Phase')->findAllWithSteps();
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);

        return $this->render('@App/Artist/steps.html.twig', array(
            'phases' => $phases,
            'currentContract' => $currentContract,
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/steps/new-contract-{step_id}", name="artist_new_contract")
     * @ParamConverter("step", class="AppBundle:Step", options={"id" = "step_id"})
     */
    public function newContractAction(Step $step, UserInterface $user, Artist $artist, Request $request) {

        // Only unlocked phases are allowed
        $phase = $step->getPhase();
        if($phase->getNum() > $artist->getPhase()->getNum()) {
            throw $this->createAccessDeniedException("Ce palier appartient à une phase que vous n'avez pas encore débloquée.");
        }

        $em = $this->getDoctrine()->getManager();

        // New contract creation
        $contract = new ContractArtist();
        $contract->setArtist($artist)
            ->setStep($step); // This needs to be done here as it is used in the formBuilder

        $th_date = new \DateTime;
        $th_date->modify('+ ' . $step->getDeadlineDuration() . ' days');
        $contract->setTheoriticalDeadline($th_date);

        $form = $this->createForm(ContractArtistType::class, $contract);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $deadline = new \DateTime();
            $deadline->modify('+ ' . $step->getDeadlineDuration() . ' days');
            $contract->setDateEnd($deadline);

            // We check that there doesn't exist another contract for that artist before DB insertion
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
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/contracts", name="artist_contracts")
     */
    public function contractsAction(UserInterface $user, Artist $artist) {

        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findBy(array('artist' => $artist), array('dateEnd' => 'DESC'));

        return $this->render('@App/Artist/contracts.html.twig', array(
           'contracts' => $contracts,
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/contract-{contract_id}", name="artist_contract")
     * @ParamConverter("contract", class="AppBundle:ContractArtist", options={"id" = "contract_id"})
     */
    public function contractAction(UserInterface $user, Artist $artist, ContractArtist $contract) {

        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findBy(array('artist' => $artist), array('dateEnd' => 'DESC'));

        return $this->render('@App/Fan/artist_contract.html.twig', array(
            'contract' => $contract,
            'artist' => $artist,
        ));
    }


    // AJAX ------------------------------------------------------------------------------------------------

    /**
     * @Route("/api/update-motivations", name="artist_ajax_update_motivations")
     */
    public function updateMotivations(Request $request, UserInterface $user, Artist $artist) {
        $em = $this->getDoctrine()->getManager();

        $motivations = $request->request->get('motivations');
        $contract_id = $request->request->get('id_contract');

        $contract = $em->getRepository('AppBundle:ContractArtist')->find($contract_id);

        if($contract->getArtist()->getId() != $artist->getId()) {
            throw $this->createAccessDeniedException("Interdit, vous n'êtes pas l'artiste à l'origine de ce contrat.");
        }

        $contract->setMotivations($motivations);
        $em->persist($contract);
        $em->flush();

        return new Response($motivations);
    }
}
