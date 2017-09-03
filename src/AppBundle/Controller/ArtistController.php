<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArtistOwnershipRequest;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Step;
use AppBundle\Entity\Artist_User;
use AppBundle\Form\Artist_UserType;
use AppBundle\Form\ArtistOwnershipsType;
use AppBundle\Form\ArtistType;
use AppBundle\Form\ContractArtistType;
use AppBundle\Services\MailTemplateProvider;
use Genemu\Bundle\FormBundle\Form\JQuery\Type\Select2Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Entity\Artist;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
        //$phases = $em->getRepository('AppBundle:Phase')->findAllWithSteps();
        $steps = $em->getRepository('AppBundle:Step')->findOrderedStepsWithoutPhases();
        $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($artist);

        return $this->render('@App/Artist/steps.html.twig', array(
            //'phases' => $phases,
            'steps' => $steps,
            'currentContract' => $currentContract,
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/steps/new-contract-{step_id}", name="artist_new_contract")
     * @ParamConverter("step", class="AppBundle:Step", options={"id" = "step_id"})
     */
    public function newContractAction(Step $step, UserInterface $user, Artist $artist, Request $request) {

        // Only unlocked phases are allowed (for later)
        /*$phase = $step->getPhase();
        if($phase->getNum() > $artist->getPhase()->getNum()) {
            throw $this->createAccessDeniedException("Ce palier appartient à une phase que vous n'avez pas encore débloquée.");
        }*/

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

    /**
     * @Route("/owners", name="artist_owners")
     */
    public function ownersAction(UserInterface $user, Artist $artist, Request $HTTPRequest) {

        if(!$user->owns($artist)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $currentOwner = $em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]);
        $owners = $artist->getArtistsUser();
        $requests = $artist->getOwnershipRequests();

        $form1 = $this->createForm(Artist_UserType::class, $currentOwner);
        $form2 = $this->createForm(ArtistOwnershipsType::class, $artist);

        $form1->handleRequest($HTTPRequest);
        $form2->handleRequest($HTTPRequest);

        if($form1->isSubmitted() && $form1->isValid()) {
            $em->persist($currentOwner);
            $em->flush();

            $this->addFlash('notice', 'ok1');
        }

        elseif($form2->isSubmitted() && $form2->isValid()) {
            $reqs = array();

            $haystack = array_map(function($elem) {
               return $elem->getUser()->getEmail();
            }, $owners->toArray());

            $haystack = array_merge($haystack, array_map(function($elem) {
                return $elem->getEmail();
            }, $requests->toArray()));

            foreach($form2->getData()->ownership_requests_form as $request) {
                /** @var ArtistOwnershipRequest $request */
                if(!(in_array($request->getEmail(), $haystack))) {
                    $request->setDemander($user);
                    $request->setArtist($artist);
                    $em->persist($request);
                    $reqs[] = $request;
                    $haystack[] = $request->getEmail();
                }
            }

            $em->flush();

            $mailer = $this->get('azine_email_template_twig_swift_mailer');
            $from = "no-reply@un-mute.be";
            $fromName = "Un-Mute";

            // Unique code is based on the id so we need this loop
            foreach($reqs as $req) {
                $params = ['artist' => $artist, 'request' => $req];

                $req->generateUniqueCode();
                $toName = '';

                $possible_user = $em->getRepository('AppBundle:User')->findOneBy(['email'=>$req->getEmail()]);
                if($possible_user != null) {
                    $template = MailTemplateProvider::OWNERSHIPREQUEST_MEMBER_TEMPLATE;
                    $params['user'] = $possible_user->getEmail();
                    $toName = $possible_user->getDisplayName();
                }
                else {
                    $template = MailTemplateProvider::OWNERSHIPREQUEST_NONMEMBER_TEMPLATE;
                }

                $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, $req->getEmail(), $toName, array(), '',
                    [], '', [], '', $params, $template);
            }

            $em->flush(); // For unique code !!

            $this->addFlash('notice', 'ok2');
        }

        return $this->render('@App/Artist/owners.html.twig', array(
            'currentOwner' => $currentOwner,
            'artist' => $artist,
            'owners' => $owners,
            'requests' => $requests,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
        ));
    }

    /**
     * @Route("/validate-ownership/{code}", name="artist_validate_ownership")
     */
    public function validateOwnershipAction(Request $request, UserInterface $user, Artist $artist, $code) {

        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findOneBy(['code' => $code]);

        if($req == null) {
            throw $this->createNotFoundException();
        }

        $mailUser = $em->getRepository('AppBundle:User')->findOneBy(['email' => $req->getEmail()]);
        if($mailUser != null) {
            // Manually log out if another user is logged in, then redirect to here
            // see https://stackoverflow.com/questions/28827418/log-user-out-in-symfony-2-application-when-remember-me-is-enabled/28828377#28828377
            if($mailUser->getId() != $user->getId()) {
                // Logging user out.
                $this->get('security.token_storage')->setToken(null);

                // Invalidating the session.
                $session = $request->getSession();
                $session->invalidate();

                // Redirecting user to login page in the end.
                $response = $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));

                // Clearing the cookies.
                $cookieNames = [
                    $this->container->getParameter('session.name'),
                    $this->container->getParameter('session.remember_me.name'),
                ];
                foreach ($cookieNames as $cookieName) {
                    $response->headers->clearCookie($cookieName);
                }

                return $response;
            }
        }

        $form = $this->createFormBuilder()
            ->add('accept', SubmitType::class)
            ->add('refuse', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('accept')->isClicked()) {
                $req->setAccepted(true);

                $artist_user = new Artist_User();
                $artist_user
                    ->setArtist($artist)
                    ->setUser($user);
                $em->persist($artist_user);
                $em->flush();
            }
            elseif($form->get('refuse')->isClicked()) {
                $req->setRefused(true);
            }

            $this->addFlash('notice', 'bien reçu');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('@App/Artist/validate_ownership.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/leave", name="artist_leave")
     */
    public function leaveAction(UserInterface $user, Artist $artist) {

        if(!$user->owns($artist)) {
            throw $this->createAccessDeniedException();
        }

        if(count($artist->getArtistsUser()) == 0) {
            $lastOne = true;
        }
        else {
            $lastOne = false;
            $em = $this->getDoctrine()->getManager();
            $em->remove($em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]));
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('@App/Artist/left.html.twig', array(
            'lastOne' => $lastOne,
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
