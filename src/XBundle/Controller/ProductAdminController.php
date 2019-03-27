<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseAdminController;
use XBundle\Entity\Product;
use AppBundle\Services\MailDispatcher;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductAdminController extends BaseAdminController
{
    public function validateAction(Request $request) {

        /** @var Product $product */
        $product = $this->admin->getSubject();

        if (!$product) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $product->getId()));
        }


        if ($product->getValidated()) {
            $this->addFlash('sonata_flash_error', 'La mise en vente de cet article est déjà validée.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, array(
                'label' => 'Valider la mise en vente de l\'article',
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
                $product->setValidated(true);
                $em->persist($product);
                $em->flush();

                $message = "La mise en vente de l'article a été validée et un mail a été envoyé aux gestionnaires du projet";

                try {
                    $this->get(MailDispatcher::class)->sendProductValidated($product);
                }
                catch(\Exception $e) {
                    $message = "La mise en vente de l'article a bien été validée mais le mail qui devait avertir les gestionnaires du projet n'est pas parti";
                }
                finally {
                    $this->addFlash('sonata_flash_success', $message);
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }            
        }

        return $this->render('@X/Admin/Product/action_validate_product.html.twig', array(
            'product' => $product,
            'form' => $form->createView()
        ));
        

    }


    public function refuseAction(Request $request)
    {
        /** @var Product $product */
        $product = $this->admin->getSubject();

        if (!$product) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $product->getId()));
        }


        if ($product->getDeleted()) {
            $this->addFlash('sonata_flash_error', 'La mise en vente de cet article a déjà été refusée.');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('reason', 'ckeditor', array(
                'label' => 'Cause(s) du refus',
                'config_name' => 'bbcode'
            ))
            ->add('confirm', SubmitType::class, array(
                'label' => 'Refuser la mise en vente de cet article',
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
                $reason = $form->get('reason')->getData();
                
                $em = $this->getDoctrine()->getManager();
                $product->setDeleted(true);
                $em->persist($product);
                $em->flush();

                $message = "La mise en vente de l'article a été refusée et un mail a été envoyé aux gestionnaires du projet pour leur expliquer les raisons de ce refus";

                try {
                    $this->get(MailDispatcher::class)->sendProductRefused($product, $reason);
                }
                catch(\Exception $e) {
                    $message = "La mise vente de l'article a bien été refusée mais le mail qui devait avertir les gestionnaires du projet n'est pas parti";
                }
                finally {
                    $this->addFlash('sonata_flash_success', $message);
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
            }            
        }

        return $this->render('@X/Admin/Product/action_refuse_product.html.twig', array(
            'product' => $product,
            'form' => $form->createView()
        ));
    }

}