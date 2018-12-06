<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Translation\TranslatorInterface;

class AnonymousActionsController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Ownership request validation by (possibly newly subscribed) user
     * @Route("/validate-ownership-{id}/{code}", name="artist_validate_ownership")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function validateOwnershipAction(Request $request, UserInterface $user = null, Artist $artist, $code, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findOneBy(['code' => $code]);

        if ($req == null) {
            throw $this->createNotFoundException('There is no request with such code');
        }

        if ($req->getAccepted() || $req->getRefused()) {
            throw $this->createNotFoundException('Request is already accepted or refused');
        }

        $mailUser = $em->getRepository('AppBundle:User')->findOneBy(['email' => $req->getEmail()]);
        if ($user != null) {
            # Manually log out if another user is logged in, then redirect to here
            # see https://stackoverflow.com/questions/28827418/log-user-out-in-symfony-2-application-when-remember-me-is-enabled/28828377#28828377
            if ($mailUser == null || $mailUser->getId() != $user->getId()) {
                # Logging user out.
                $this->get('security.token_storage')->setToken(null);

                # Invalidating the session.
                $session = $request->getSession();
                $session->invalidate();

                # Redirecting user to login page in the end.
                $response = $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));

                # Clearing the cookies.
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
            ->add('accept', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'labels.ownershiprequest.accept',
            ))
            ->add('refuse', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-secondary'],
                'label' => 'labels.ownershiprequest.refuse',
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$req->getCancelled()) {
            if ($form->get('accept')->isClicked()) {
                $req->setAccepted(true);

                $artist_user = new Artist_User();
                $artist_user
                    ->setArtist($artist)
                    ->setUser($user);
                $em->persist($artist_user);
                $em->flush();
                $this->addFlash('notice', $translator->trans('notices.artist_ownership_request_accepted', ['%artist%' => $artist->getArtistname()]));
            } elseif ($form->get('refuse')->isClicked()) {
                $req->setRefused(true);
                $em->flush();
                $this->addFlash('notice', 'notices.artist_ownership_request_refused');
            }

            return $this->redirectToRoute('homepage');
        }
        return $this->render('@App/User/Artist/validate_ownership.html.twig', array(
            'form' => $form->createView(),
            'request' => $req,
        ));
    }

    /**
     * Email change request validation by requesting user
     * @Route("/change-email-token-{token}", name="user_change_email_check")
     */
    public function changeEmailCheckAction(Request $request, UserInterface $current_user = null, $token)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['asked_email_token' => $token]);

        if (!$user) {
            $this->addFlash('error', 'errors.change_email_token_expired');
            return $this->redirectToRoute('homepage');
        }

        $asked_email = $user->getAskedEmail();

        $error_detector = $em->getRepository('AppBundle:User')->findOneBy(['email' => $asked_email]);
        if ($error_detector != null) {
            $this->addFlash('error', 'errors.change_email_used_since');
            return $this->redirectToRoute('homepage');
        }

        # Everything ok -> let's change email
        $user->setEmail($asked_email);
        $user->setEmailCanonical($asked_email);

        $user->setAskedEmail(null);
        $user->setAskedEmailToken(null);

        # Logout (in case another user was logged in)
        if ($current_user != null && $current_user->getId() != $user->getId()) {
            $this->get('security.token_storage')->setToken(null);

            # Invalidating the session.
            $session = $request->getSession();
            $session->invalidate();

            $this->addFlash('notice', 'notices.change_email_logged_out');
        } else {
            $this->addFlash('notice', 'notices.change_email');
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}