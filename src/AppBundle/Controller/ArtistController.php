<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArtistOwnershipRequest;
use AppBundle\Entity\Artist_User;
use AppBundle\Form\ArtistMediasType;
use AppBundle\Form\ArtistOwnershipsType;
use AppBundle\Form\ArtistType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Artist;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class ArtistController extends BaseController
{
    /**
     * Edit profile: form to change artist general info
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

            $this->addFlash('notice', 'notices.edition');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/edit_profile.html.twig', array(
            'artist' => $artist,
            'photo' => $photo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Owners: lists artist owners and offers possibility to add one
     * @Route("/owners", name="artist_owners")
     */
    public function ownersAction(UserInterface $user, Artist $artist, Request $HTTPRequest) {
        $this->assertOwns($user, $artist);

        $em = $this->getDoctrine()->getManager();

        // Get all owwners and ownership requests
        $owners = $artist->getArtistsUser();
        $requests = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist, 'cancelled' => false, 'refused' => false, 'accepted' => false]);

        // Form to create additional requests
        $form = $this->createForm(ArtistOwnershipsType::class, $artist);

        $form->handleRequest($HTTPRequest);

        if($form->isSubmitted() && $form->isValid()) {

            $reqs = array();

            $haystack = array_map(function (Artist_User $elem) {
                return $elem->getUser()->getEmail();
            }, $owners->toArray());

            $haystack = array_merge($haystack, array_map(function (ArtistOwnershipRequest $elem) {
                return $elem->getEmail();
            }, $requests));

            # Check if submitted requests are not for already artist-related emails
            foreach ($form->getData()->ownership_requests_form as $request) {
                /** @var ArtistOwnershipRequest $request */
                if (!(in_array($request->getEmail(), $haystack))) {
                    $request->setDemander($user);
                    $request->setArtist($artist);
                    $em->persist($request);
                    $reqs[] = $request;
                    $haystack[] = $request->getEmail();
                }
            }

            // Flush to save requests
            $em->flush();

            // Unique code is based on the id so we need this loop
            foreach ($reqs as $req) {
                $req->generateUniqueCode();
                $this->mailDispatcher->sendNewOwnershipRequest($artist, $req);
            }

            // Second flush for unique codes
            $em->flush();

            $this->addFlash('notice', 'notices.artist_ownership_requests');

            return $this->redirectToRoute($HTTPRequest->get('_route'), $HTTPRequest->get('_route_params'));
        }

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/owners.html.twig', array(
            'artist' => $artist,
            'photo' => $photo,
            'owners' => $owners,
            'requests' => $requests,
            'form' => $form->createView(),
        ));
    }


    /**
     * Cancel Ownership Request: Deletes given request, actually marking it as "cancelled"
     * @Route("/cancel-request/{request_id}", name="artist_cancel_ownership_request")
     * @ParamConverter("o_request", class="AppBundle:ArtistOwnershipRequest", options={"id" = "request_id"})
     */
    public function cancelOwnershipRequestAction(UserInterface $user, Artist $artist, ArtistOwnershipRequest $o_request) {
        $this->assertOwns($user, $artist);

        # Canceler must be demander
        if($o_request->getDemander() != $user) {
            throw $this->createAccessDeniedException("You didn't emit this ownership request.");
        }

        $o_request->setCancelled(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($o_request);
        $em->flush();
        $this->addFlash('notice', 'notices.artist_ownership_request_cancel');

        return $this->redirectToRoute('artist_owners', array(
            'id' => $artist->getId(),
        ));
    }

    /**
     * Leave: displays a confirmation window before allowing user to leave his artist
     * and marks artist as deleted if he was the last one
     * This action doesn't do anything (except showing an error message) if artist is currently selling tickets and user is its last owner
     * @Route("/leave", name="artist_leave")
     */
    public function leaveAction(Request $request, UserInterface $user, Artist $artist, TranslatorInterface $translator) {
        $this->assertOwns($user, $artist);

        $lastOne = count($artist->getArtistsUser()) == 1;

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, [
                'label' => $translator->trans('labels.artist.leave.submit', ['%artist%' => $artist->getArtistname()]),
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('confirm')->isClicked()) {

                if($lastOne && !$artist->canBeLeft()) {
                    // TODO translate
                    $form->addError(new FormError('Vous ne pouvez pas quitter un artiste lorsqu\'un événement de récolte de tickets est en cours pour cet artiste.'));
                }

                else {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($em->getRepository('AppBundle:Artist_User')->findOneBy(['user' => $user, 'artist' => $artist]));

                    if($lastOne) {
                        $this->suppressArtist($artist);
                    }
                    $em->flush();

                    if($lastOne) {
                        $this->addFlash('notice', $translator->trans('notices.artist_leave_last', ['%artist%' => $artist->getArtistname()]));
                    }
                    else {
                        $this->addFlash('notice', $translator->trans('notices.artist_leave', ['%artist%' => $artist->getArtistname()]));
                    }
                    return $this->redirectToRoute('user_my_artists');
                }
            }
            elseif($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('artist_owners', ['id' => $artist->getId()]);
            }
        }

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/leave.html.twig', array(
            'lastOne' => $lastOne,
            'artist' => $artist,
            'photo' => $photo,
            'form' => $form->createView(),
        ));

    }

    /**
     * Edit Profile Pic: form to add one primary picture
     * @Route("/edit-profile-picture", name="artist_edit_profilepic")
     */
    public function editProfilepicAction(UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/edit_profilepic.html.twig', array(
            'artist' => $artist,
            'photo' => $photo,
        ));
    }

    /**
     * Edit photos: form to add/remove photos
     * @Route("/edit-photos", name="artist_edit_photos")
     */
    public function editPhotosAction(UserInterface $user, Artist $artist) {
        $this->assertOwns($user, $artist);

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/edit_photos.html.twig', array(
            'artist' => $artist,
            'photo' => $photo,
        ));
    }

    /**
     * Edit medias: form to add/remove/edit videos
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

            $this->addFlash('notice', 'notices.edition');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        if (!empty($artist->getAllPhotos())){
            $photo = $artist->getAllPhotos()[0];
        }
        else {
            $photo = null;
        }

        return $this->render('@App/User/Artist/edit_medias.html.twig', array(
            'artist' => $artist,
            'photo' => $photo,
            'form' => $form->createView(),
        ));
    }

    // AJAX ------------------------------------------------------------------------------------------------

    /**
     * Remove Photo: removes photo of given filename
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