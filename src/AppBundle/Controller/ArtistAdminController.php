<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Services\MailDispatcher;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArtistAdminController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->configure();
    }

    public function validateAction(Request $request) {

        /** @var Artist $artist */
        $artist = $this->admin->getSubject();

        if (!$artist) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $artist->getId()));
        }

        if($artist->getValidated()) {
            $this->addFlash('sonata_flash_error', 'Cet artiste est déjà validé.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, array(
                'label' => 'Valider & rendre public',
                'attr' => array('class' => 'btn btn-success')
            ))
            ->add('cancel', SubmitType::class, array(
                'label' => 'Annuler',
                'attr' => array('class' => 'btn')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));
            } elseif ($form->get('confirm')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $artist->setValidated(true)->setVisible(true);
                $em->persist($artist);
                $em->flush();

                $message = "L'artiste est visible et a reçu un mail qui l'en informe.";
                try {
                    $this->get(MailDispatcher::class)->sendArtistValidated($artist);
                }
                catch(\Exception $e) {
                    $message = "L'artiste a bien été rendu visible mais le mail qui devait l'en avertir n'est pas parti - à checker avec le webmaster.";
                }
                finally {
                    $this->addFlash('sonata_flash_success', $message);
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }
        }

        return $this->render('@App/Admin/Artist/action_validate.html.twig', array(
            'artist' => $artist,
            'form' => $form->createView(),
        ));
    }
}