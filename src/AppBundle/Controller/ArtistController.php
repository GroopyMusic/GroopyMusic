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
use Spipu\Html2Pdf\Tag\Sub;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
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
        $requests = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist, 'cancelled' => false, 'refused' => false, 'accepted' => false]);

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
            throw $this->createNotFoundException('There is no request with such code');
        }

        if($req->getAccepted() || $req->getRefused()) {
            throw $this->createAccessDeniedException('Request is already accepted or refused');
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

        if($form->isSubmitted() && !$req->getCancelled()) {
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
            'request' => $req,
        ));
    }

    /**
     * @Route("/cancel-request/{request_id}", name="artist_cancel_ownership_request")
     * @ParamConverter("o_request", class="AppBundle:ArtistOwnershipRequest", options={"id" = "request_id"})
     */
    public function cancelOwnershipRequestAction(UserInterface $user, Artist $artist, ArtistOwnershipRequest $o_request) {
        if(!$user->owns($artist) || $o_request->getDemander() != $user) {
            throw $this->createAccessDeniedException("You don't own this artist, or you didn't emit this ownership request.");
        }

        $o_request->setCancelled(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($o_request);
        $em->flush();
        $this->addFlash('notice', 'Requête supprimée');

        return $this->redirectToRoute('artist_owners', array(
            'id' => $artist->getId(),
        ));
    }

    /**
     * @Route("/leave", name="artist_leave")
     */
    public function leaveAction(Request $request, UserInterface $user, Artist $artist) {

        if(!$user->owns($artist)) {
            throw $this->createAccessDeniedException();
        }

        $lastOne = count($artist->getArtistsUser()) == 1;

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class)
            ->add('cancel', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('confirm')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]));
                if($lastOne) {
                    $artist->setDeleted(true);
                    $em->persist($artist);
                }
                $em->flush();

                if($lastOne) {
                    $this->addFlash('notice', 'Bien reçu. Vous êtiez le dernier propriétaire de ' . $artist->getArtistname() . ' donc l\'artiste a été supprimé.');
                }
                else {
                    $this->addFlash('notice', 'Bien reçu.');
                }
                return $this->redirectToRoute('homepage');
            }
            elseif($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('artist_owners', ['id' => $artist->getId()]);
            }
        }

        return $this->render('@App/Artist/leave.html.twig', array(
            'lastOne' => $lastOne,
            'artist' => $artist,
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/photos", name="artist_edit_photos")
     */
    public function photosAction(Artist $artist) {
        return $this->render('@App/Artist/photos.html.twig', array(
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

    /**
     * @Route("/api/remove-photo", name="artist_ajax_remove_photo")
     */
    public function removePhotoAction(Request $request, UserInterface $user, Artist $artist) {

        $em = $this->getDoctrine()->getManager();

        $filename = $request->get('filename');
        $pp = boolval($request->get('pp', false));

        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['filename' => $filename]);

        $em->remove($photo);

        if($pp) {
            $artist->setProfilepic(null);
        }
        else {
            $artist->removePhoto($photo);
        }

        $filesystem = new Filesystem();
        $filesystem->remove($this->get('kernel')->getRootDir().'/../web/' . $photo->getWebPath());

        $em->persist($artist);
        $em->flush();

        return new Response();
    }
}
