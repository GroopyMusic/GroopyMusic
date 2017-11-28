<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArtistOwnershipRequest;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Artist_User;
use AppBundle\Form\Artist_UserType;
use AppBundle\Form\ArtistMediasType;
use AppBundle\Form\ArtistOwnershipsType;
use AppBundle\Form\ArtistType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Artist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ArtistController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function assertOwns(UserInterface $user, Artist $artist) {
        if(!$user->owns($artist)) {
            throw $this->createAccessDeniedException("You don't own this artist!");
        }
    }

    /**
     * @Route("/edit", name="artist_profile_edit")
     */
    public function editProfileAction(Request $request, UserInterface $user, Artist $artist) {

        $this->assertOwns($user, $artist);

        $form = $this->createForm(ArtistType::class, $artist, ['edit' => true]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($artist);
            $em->flush();

            $this->addFlash('notice', 'Les modifications ont été enregistrées.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@App/User/Artist/edit_profile.html.twig', array(
            'artist' => $artist,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/all-contracts", name="artist_contracts")
     */
    public function contractsAction(UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findBy(array('artist' => $artist), array('dateEnd' => 'DESC'));

        return $this->render('@App/User/Artist/all_contracts.html.twig', array(
            'contracts' => $contracts,
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/owners", name="artist_owners")
     */
    public function ownersAction(UserInterface $user, Artist $artist, Request $HTTPRequest) {
        $this->assertOwns($user, $artist);

        $em = $this->getDoctrine()->getManager();

        $currentOwner = $em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]);
        $owners = $artist->getArtistsUser();
        $requests = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist, 'cancelled' => false, 'refused' => false, 'accepted' => false]);

        $form1 = $this->createForm(Artist_UserType::class, $currentOwner);
        $form2 = $this->createForm(ArtistOwnershipsType::class, $artist);

        $form2->handleRequest($HTTPRequest);

        if($HTTPRequest->getMethod() == 'POST') {

            /*if ($HTTPRequest->request->has($form1->getName())) {
                $form1->handleRequest($HTTPRequest);

                if ($form1->isValid()) {
                    $em->persist($currentOwner);
                    $em->flush();

                    $this->addFlash('notice', 'Votre rôle a bien été enregistré');
                }
            }*/
            if ($HTTPRequest->request->has($form2->getName())) {

                if ($form2->isValid()) {
                    $reqs = array();

                    $haystack = array_map(function (Artist_User $elem) {
                        return $elem->getUser()->getEmail();
                    }, $owners->toArray());

                    $haystack = array_merge($haystack, array_map(function (ArtistOwnershipRequest $elem) {
                        return $elem->getEmail();
                    }, $requests));

                    foreach ($form2->getData()->ownership_requests_form as $request) {
                        /** @var ArtistOwnershipRequest $request */
                        if (!(in_array($request->getEmail(), $haystack))) {
                            $request->setDemander($user);
                            $request->setArtist($artist);
                            $em->persist($request);
                            $reqs[] = $request;
                            $haystack[] = $request->getEmail();
                        }
                    }

                    $em->flush();

                    $mailer = $this->get('AppBundle\Services\MailDispatcher');

                    // Unique code is based on the id so we need this loop
                    foreach ($reqs as $req) {
                        $req->generateUniqueCode();
                        $mailer->sendNewOwnershipRequest($artist, $req);
                    }

                    $em->flush(); // For unique code !!

                    $this->addFlash('notice', 'Les invitations ont été envoyées.');

                    return $this->redirectToRoute($HTTPRequest->get('_route'), $HTTPRequest->get('_route_params'));
                }
            }
        }

        return $this->render('@App/User/Artist/owners.html.twig', array(
            'currentOwner' => $currentOwner,
            'artist' => $artist,
            'owners' => $owners,
            'requests' => $requests,
            'form1' => $form1->createView(),
            'form2' => $form2->createView(),
        ));
    }


    /**
     * @Route("/cancel-request/{request_id}", name="artist_cancel_ownership_request")
     * @ParamConverter("o_request", class="AppBundle:ArtistOwnershipRequest", options={"id" = "request_id"})
     */
    public function cancelOwnershipRequestAction(UserInterface $user, Artist $artist, ArtistOwnershipRequest $o_request) {
        $this->assertOwns($user, $artist);

        if($o_request->getDemander() != $user) {
            throw $this->createAccessDeniedException("You didn't emit this ownership request.");
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
        $this->assertOwns($user, $artist);

        $lastOne = count($artist->getArtistsUser()) == 1;

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, [
                'label' => 'Quitter ' . $artist->getArtistname(),
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('confirm')->isClicked()) {

                if($lastOne && !$artist->isAvailable()) {
                    $form->addError('Vous ne pouvez pas quitter un artiste lorsqu\'un événement de récolte de tickets est en cours pour cet artiste.');
                }

                else {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]));

                    if($lastOne) {
                        // Duplicated in UserController->advanced
                        $artist->setDeleted(true);

                        foreach($em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist]) as $o_request) {
                            $em->remove($o_request);
                        }
                        // End Duplicated

                        $em->persist($artist);
                    }
                    $em->flush();

                    if($lastOne) {
                        $this->addFlash('notice', 'Vous avez quitté '. $artist->getArtistname() . '. Vous êtiez le dernier propriétaire de ' . $artist->getArtistname() . ' donc l\'artiste a été supprimé.');
                    }
                    else {
                        $this->addFlash('notice', 'Vous avez quitté '. $artist->getArtistname());
                    }
                    return $this->redirectToRoute('user_my_artists');
                }
            }
            elseif($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('artist_owners', ['id' => $artist->getId()]);
            }
        }

        return $this->render('@App/User/Artist/leave.html.twig', array(
            'lastOne' => $lastOne,
            'artist' => $artist,
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/edit-profile-picture", name="artist_edit_profilepic")
     */
    public function editProfilepicAction(UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        return $this->render('@App/User/Artist/edit_profilepic.html.twig', array(
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/edit-photos", name="artist_edit_photos")
     */
    public function editPhotosAction(UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        return $this->render('@App/User/Artist/edit_photos.html.twig', array(
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/edit-medias", name="artist_edit_medias")
     */
    public function editMediasAction(Request $request, UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        $form = $this->createForm(ArtistMediasType::class, $artist);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($artist);
            $em->flush();

            $this->addFlash('notice', 'Les modifications ont bien été enregistrées.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@App/User/Artist/edit_medias.html.twig', array(
            'artist' => $artist,
            'form' => $form->createView(),
        ));
    }

    // AJAX ------------------------------------------------------------------------------------------------

    /**
     * @Route("/api/remove-photo", name="artist_ajax_remove_photo")
     */
    public function removePhotoAction(Request $request, UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

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
        $filesystem->remove($this->get('kernel')->getRootDir().'/../web/' . Artist::getWebPath($photo));

        $em->persist($artist);
        $em->flush();

        return new Response();
    }
}