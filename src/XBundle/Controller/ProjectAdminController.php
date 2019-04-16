<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseAdminController;
use XBundle\Entity\Project;
use XBundle\Entity\Product;
use AppBundle\Services\MailDispatcher;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectAdminController extends BaseAdminController
{
    public function validateAction(Request $request) {

        /** @var Project $project */
        $project = $this->admin->getSubject();

        if (!$project) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $project->getId()));
        }


        if ($project->getValidated()) {
            $this->addFlash('sonata_flash_error', 'Ce projet est déjà validé.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, array(
                'label' => 'Valider le projet',
                'attr' => array('class' => 'btn btn-success')
            ))
            ->add('cancel', SubmitType::class, array(
                'label' => 'Annuler',
                'attr' => array('class' => 'btn')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));

            } elseif ($form->get('confirm')->isClicked()) {
                $em = $this->getDoctrine()->getManager();
                $project->setValidated(true);
                $em->persist($project);
                $em->flush();

                $message = "Le projet a été validé et un mail a été envoyé aux gestionnaires du projet";

                try {
                    $this->get(MailDispatcher::class)->sendProjectValidated($project);
                }
                catch(\Exception $e) {
                    $message = "Le projet a bien été validé mais le mail qui devait avertir les gestionnaires du projet n'est pas parti";
                }
                finally {
                    $this->addFlash('sonata_flash_success', $message);
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }            
        }

        return $this->render('@X/Admin/action_validate_project.html.twig', array(
            'form' => $form->createView(),
            'project' => $project,
        ));
        

    }


    public function refuseAction(Request $request)
    {
        /** @var Project $project */
        $project = $this->admin->getSubject();

        if (!$project) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $project->getId()));
        }

        if ($project->getDeletedAt() != null) {
            $this->addFlash('sonata_flash_error', 'Ce projet a déjà été refusé.');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('reason', 'ckeditor', array(
                'label' => 'Cause(s) du refus',
                'config_name' => 'bbcode'
            ))
            ->add('confirm', SubmitType::class, array(
                'label' => 'Refuser le projet',
                'attr' => array('class' => 'btn btn-danger')
            ))
            ->add('cancel', SubmitType::class, array(
                'label' => 'Annuler',
                'attr' => array('class' => 'btn')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));

            } elseif ($form->get('confirm')->isClicked()) {
                $reason = $form->get('reason')->getData();
                
                $em = $this->getDoctrine()->getManager();

                foreach($project->getProducts() as $product) {
                    $em->remove($product);
                }

                $em->remove($project);

                $em->flush();

                $message = "Le projet a été refusé et un mail a été envoyé aux gestionnaires du projet pour leur expliquer les raisons de ce refus";
                try {
                    $this->get(MailDispatcher::class)->sendProjectRefused($project, $reason);
                }
                catch(\Exception $e) {
                    $message = "Le projet a bien été refusé mais le mail qui devait avertir les gestionnaires du projet n'est pas parti";
                }
                finally {
                    $this->addFlash('sonata_flash_success', $message);
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }
        }

        return $this->render('@X/Admin/action_refuse_project.html.twig', array(
            'form' => $form->createView(),
            'project' => $project,
        ));
    }

}