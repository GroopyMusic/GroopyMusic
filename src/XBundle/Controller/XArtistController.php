<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use XBundle\Entity\OptionProduct;
use XBundle\Entity\Product;
use XBundle\Entity\Project;
use XBundle\Entity\XTransactionalMessage;
use XBundle\Form\OptionProductType;
use XBundle\Form\ProductType;
use XBundle\Form\ProjectType;
use XBundle\Form\XTransactionalMessageType;


class XArtistController extends BaseController
{

    /**
     * @Route("/dashboard", name="x_artist_dashboard")
     */
    public function dashboardAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfArtistAuthorized($user);

        $otherCurrentProjects = null;
        $otherPassedProjects = null;
        if ($user->isSuperAdmin()) {
            $otherCurrentProjects = $em->getRepository('XBundle:Project')->getOtherCurrentProjects($user);
            $otherPassedProjects = $em->getRepository('XBundle:Project')->getOtherPassedProjects($user);
        }

        $currentProjects = $em->getRepository('XBundle:Project')->getCurrentProjects($user);
        $passedProjects = $em->getRepository('XBundle:Project')->getPassedProjects($user);

        return $this->render('@X/XArtist/dashboard_artist.html.twig', [
            'current_projects' => $currentProjects,
            'passed_projects' => $passedProjects,
            'other_current_projects' => $otherCurrentProjects,
            'other_passed_projects' => $otherPassedProjects,
        ]);
    }


    /**
     * @Route("/project/new", name="x_artist_project_new")
     */
    public function newProjectAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, MailDispatcher $mailDispatcher)
    {
        $this->checkIfArtistAuthorized($user);

        $project = new Project();

        $artistsUser = $em->getRepository('AppBundle:Artist')->findForUser($user);

        $form = $this->createForm(ProjectType::class, $project, ['creation' => true, 'artists_user' => $artistsUser]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $artist = $form->get('artist')->getData();
            $project->setArtist($artist);

            // add all artist owners to project
            $artistOwners = $em->getRepository('AppBundle:Artist_User')->getArtistOwners($artist->getId());
            foreach($artistOwners as $ao) {
                $project->addHandler($ao->getUser());
            }

            $em->persist($project);
            $em->flush();

            $mailDispatcher->sendAdminNewProject($project);
            $message = 'Le projet "' . $project->getTitle() . '" a bien été créé. Il doit maintenant être validé par l\'équipe d\'Un-Mute pour être visible par le public sur Chapots. Vous pouvez passer à la mise en vente d\'articles ou passer cet étape';
            $this->addFlash('x_notice_success_project', $message);
            return $this->redirectToRoute('x_artist_product_add', ['id' => $project->getId()]);
        }

        return $this->render('@X/XArtist/project_new.html.twig', array(
            'form' => $form->createView(),
            'project' => $project
        ));
    }


    /**
     * @Route("/passed-projects", name="x_artist_passed_projects")
     */
    public function passedProjectsAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfArtistAuthorized($user);

        $passedProjects = $em->getRepository('XBundle:Project')->getPassedProjects($user);
        $otherPassedProjects = $em->getRepository('XBundle:Project')->getOtherPassedProjects($user);

        return $this->render('@X/XArtist/passed_projects.html.twig', [
            'projects' => $passedProjects,
            'other_projects' => $otherPassedProjects
        ]);
    }


    /**
     * @Route("/project/{id}/update", name="x_artist_project_update")
     */
    public function updateProjectAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if($project == null || $project->isPassed()) {
            $this->addFlash('x_warning', "Pas possible de modifier le projet");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $artistsUser = $em->getRepository('AppBundle:Artist')->findForUser($user);
        
        $form = $this->createForm(ProjectType::class, $project, ['is_edit' => true, 'artists_user' => $artistsUser]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            $this->addFlash('x_notice', 'Le projet a bien été mis à jour.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@X/XArtist/project_new.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }


    /**
     * @Route("/project/{id}/confirm", name="x_artist_project_confirm")
     */
    public function confirmProjectAction(EntityManagerInterface $em, Project $project, UserInterface $user = null, MailDispatcher $mailDispatcher, TicketingManager $ticketingManager)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if ($project == null || $project->getCollectedAmount() == 0 || $project->getSuccessful()) {
            $this->addFlash('x_warning', "Pas possible de confirmer le projet");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $mailDispatcher->sendConfirmedProject($project);

        // Generate and send tickets
        foreach($project->getSalesPaid() as $sale) {
            if(!empty($sale->getTicketsPurchases())) {
                $ticketingManager->generateAndSendXTickets($sale);
            }
        }

        $project->setSuccessful(true);
        $em->flush();

        $this->addFlash('x_notice', "Le projet a bien été confirmé. Les contributeurs ont été avertis et les éventuels tickets vendus ont été envoyés");
        return $this->redirectToRoute('x_artist_dashboard');
    }


    /**
     * @Route("/project/{id}/refund", name="x_artist_project_refund")
     */
    public function refundProjectAction(EntityManagerInterface $em, Project $project, UserInterface $user = null, PaymentManager $paymentManager)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if ($project == null || $project->getCollectedAmount() == 0 || $project->getFailed() || $project->getRefunded()) {
            $this->addFlash('x_warning', "Pas possible d'annuler le projet");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $project->setFailed(true);
        $paymentManager->refundStripeAndProject($project);
        $em->flush();

        $this->addFlash('x_notice', 'Le projet a bien été annulé. Les éventuels contributeurs ont été avertis et remboursés.');
        return $this->redirectToRoute('x_artist_dashboard');
    }


    /**
     * @Route("/project/{id}/delete", name="x_artist_project_delete")
     */
    public function deleteProjectAction(EntityManagerInterface $em, Project $project, UserInterface $user = null)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if ($project == null || $project->getCollectedAmount() > 0 || $project->getDeletedAt() != null) {
            $this->addFlash('x_warning', "Pas possible de supprimer le projet.");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        // Remove products
        foreach ($project->getProducts() as $product) {
            $em->remove($product);
        }

        $em->remove($project);
        $em->flush();

        $this->addFlash('x_notice', 'Le projet a bien été supprimé');
        return $this->redirectToRoute('x_artist_dashboard');

    }


    /**
     * @Route("/project/{id}/contributions", name="x_artist_project_contributions")
     */
    public function contributionsProjectAction(UserInterface $user = null, Project $project)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if($project == null) {
            $this->addFlash('x_warning', "Le projet n'existe pas");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $donations = array_reverse($project->getDonationsPaid());
        $sales = array_reverse($project->getSalesPaid());

        return $this->render('@X/XArtist/project_contributions.html.twig', array(
            'project' => $project,
            'donations' => $donations,
            'sales' => $sales
        ));
    }


    /**
     * @Route("/project/{id}/products", name="x_artist_project_products")
     */
    public function productsProjectAction(EntityManagerInterface $em, UserInterface $user = null, Project $project)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if($project == null) {
            $this->addFlash('x_warning', "Le projet n'existe pas");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $products = $em->getRepository('XBundle:Product')->getProductsForProject($project);
        
        return $this->render('@X/XArtist/Product/products.html.twig', array(
            'project' => $project,
            'products' => $products
        ));
    }


    /**
     * @Route("/project/{id}/product/add", name="x_artist_product_add")
     */
    public function addProductAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project, MailDispatcher $mailDispatcher)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if($project == null || $project->isPassed()) {
            $this->addFlash('x_warning', "Pas possible d'ajouter un article");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $product = new Product();
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $product->setProject($project);
            $em->persist($product);
            $em->flush();

            $mailDispatcher->sendAdminNewProduct($product);
            $message = "La mise en vente de l'article '" . $product->getName() . "' a bien été enregistrée! ";
            if ($product->isTicket()) {
                $message .= "Il doit maintenant être validé par les administrateurs d'Un-Mute";
                $this->addFlash('x_notice', $message);
                return $this->redirectToRoute('x_artist_project_products', ['id' => $project->getId()]);
            } else {
                $message .= "En attendant sa validation par les administrateurs d'Un-Mute, vous pouvez lui ajouter des options (ex: taille, couleur, ...) ou passer cette étape";
                $this->addFlash('x_notice_success_product', $message);
                return $this->redirectToRoute('x_artist_product_update', ['id' => $project->getId(), 'idProd' => $product->getId()]);
            }
        }

        return $this->render('@X/XArtist/Product/product_add.html.twig', array(
            'form' => $form->createView(),
            'project' => $project,
            'product' => $product
        ));
    }


    /**
     * @Route("/project/{id}/product/{idProd}/update", name="x_artist_product_update")
     */
    public function updateProductAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project, $idProd)
    {
        $this->checkIfArtistAuthorized($user, $project);

        $product = $em->getRepository('XBundle:Product')->find($idProd);

        if ($project == null || $project->isPassed() || $product == null) {
            $this->addFlash('x_warning', "Pas possible de modifier l'article");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            $this->addFlash('x_notice', 'L\'article a bien été modifié.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@X/XArtist/Product/product_add.html.twig', array(
            'form' => $form->createView(),
            'project' => $project,
            'product' => $product
        ));
    }


    /**
     * @Route("/project/{id}/product/{idProd}/delete", name="x_artist_product_delete")
     */
    public function deleteProductAction(EntityManagerInterface $em, UserInterface $user = null, Project $project, $idProd)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if ($project == null || $project->getCollectedAmount() > 0 || $project->getDeletedAt() != null) {
            $this->addFlash('x_warning', "Pas possible de supprimer le projet.");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $product = $em->getRepository('XBundle:Product')->find($idProd);

        if($project == null || $product == null || $product->getProductsSold() > 0 || $product->getDeletedAt() != null) {
            throw $this->createNotFoundException("Pas possible de supprimer le produit");
        }

        $em->remove($product);
        $em->flush();

        $this->addFlash('x_notice', 'L\'article a bien été supprimé');
        return $this->redirectToRoute('x_artist_project_products', ['id' => $project->getId()]);
    }


    /**
     * @Route("/product/{id}/options", name="x_product_options")
     */
    public function getProductOptionsAction(UserInterface $user = null, Product $product)
    {
        $this->checkIfArtistAuthorized($user, $product->getProject());

        return new Response($this->renderView('@X/XArtist/Product/product_options.html.twig', [
            'product' => $product,
        ]));
    }


    /**
     * @Route("/product/{id}/create-option", name="x_product_create_option")
     */
    public function createOptionAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Product $product)
    {
        $this->checkIfArtistAuthorized($user, $product->getProject());

        $option = new OptionProduct();
        $option->setProduct($product);

        $form = $this->createForm(OptionProductType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $option->setName($form->get('name')->getData());
            foreach ($form->get('choices')->getData() as $choice) {
                $option->addChoice($choice);
            }
            $em->persist($option);
            $em->flush();
            return new Response('OK');
        }
        else {
            return new Response($this->renderView('@X/Form/option_create.html.twig', [
                'form' => $form->createView(),
                'product' => $product,
            ]));
        }
    }


    /**
     * @Route("/product/update-option/{id}", name="x_product_update_option")
     */
    public function updateOptionAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, OptionProduct $option)
    {
        $product = $option->getProduct();

        $this->checkIfArtistAuthorized($user, $product->getProject());

        $form = $this->createForm(OptionProductType::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $option->setName($form->get('name')->getData());
            foreach ($form->get('choices')->getData() as $choice) {
                $option->addChoice($choice);
            }
            $em->persist($option);
            $em->flush();
            return new Response('OK');
        }
        else {
            return new Response($this->renderView('@X/Form/option_update.html.twig', [
                'form' => $form->createView(),
                'product' => $product,
                'option' => $option
            ]));
        }
    }

    /**
     * @Route("/product/delete-option/{id}", name="x_product_delete_option")
     */
    public function deleteOptionAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, OptionProduct $option)
    {
        $product = $option->getProduct();

        $this->checkIfArtistAuthorized($user, $product->getProject());

        if($request->getMethod() == 'POST') {
            $em->remove($option);
            $em->flush();
            return new Response('OK');
        }
        else {
            return new Response($this->renderView('@X/Form/option_delete.html.twig', [
                'option' => $option,
                'product' => $product
            ]));
        }
    }


    /**
     * @Route("/project/{id}/transactional-message", name="x_artist_project_transactional_message")
     */
    public function transactionalMessageProjectAction(EntityManagerInterface $em, Request $request, UserInterface $user = null, Project $project)
    {
        $this->checkIfArtistAuthorized($user, $project);

        if($project == null) {
            $this->addFlash('x_warning', "Le projet n'existe pas");
            return $this->redirectToRoute('x_artist_dashboard');
        }

        $message = new XTransactionalMessage($project);
        $oldMessages = array_reverse($project->getTransactionalMessages()->toArray());

        $form = $this->createForm(XTransactionalMessageType::class, $message);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            
            // if project has donators and buyers 
            if(!empty($project->getDonators()) && !empty($project->getBuyers())) {
                // if check donators
                if ($form->get('toDonators')->getData() == true && $form->get('toBuyers')->getData() == false) {
                    $contributors = $project->getDonators($form->get('beforeValidation')->getData());
                }
                // if check buyers
                elseif ($form->get('toDonators')->getData() == false && $form->get('toBuyers')->getData() == true) {
                    $contributors = $project->getBuyers($form->get('beforeValidation')->getData(), $form->get('products')->getData());
                }
                // else all contributors
                else {
                    $message->setToDonators(true);
                    $message->setToBuyers(true);
                    $contributors = $project->getContributors($form->get('beforeValidation')->getData(), $form->get('products')->getData());
                }
            }
            else {
                // if project has only buyers
                if (!empty($project->getBuyers())) {
                    $message->setToBuyers(true);
                    $contributors = $project->getBuyers($form->get('beforeValidation')->getData(), $form->get('products')->getData());
                } 
                // if project has only donators
                elseif(!empty($project->getDonators())) {
                    $message->setToDonators(true);
                    $contributors = $project->getDonators($form->get('beforeValidation')->getData());
                }   
            }

            $em->persist($message);
            $em->flush();

            $this->mailDispatcher->sendXTransactionalMessageWithCopy($message, $contributors);

            $this->addFlash('x_notice', 'Votre message a bien été envoyé.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@X/XArtist/project_transactional_message.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
            'old_messages' => $oldMessages,
        ]);

    }


    /**
     * @Route("/project/{id}/{code}remove-photo", name="x_artist_project_remove_photo")
     */
    public function removePhotoAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project, $code)
    {
        $filename = $request->get('filename');
        $photo = $em->getRepository('XBundle:Image')->findOneBy(['filename' => $filename]);
        
        $em->remove($photo);
        $project->removeProjectPhoto($photo);
        
        $filesystem = new Filesystem();
        $filesystem->remove($this->get('kernel')->getRootDir().'/../web/' . Project::getWebPath($photo));
        
        $em->persist($project);
        $em->flush();
        return new Response();
    }

}

?>