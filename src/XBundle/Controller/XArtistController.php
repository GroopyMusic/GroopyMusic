<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Services\MailDispatcher;
use Doctrine\ORM\EntityManagerInterface;
//use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use XBundle\Entity\Product;
use XBundle\Entity\Project;
use XBundle\Form\ProductType;
use XBundle\Form\ProjectType;

class XArtistController extends BaseController
{

    /**
     * @Route("/dashboard", name="x_artist_dashboard")
     */
    public function dashboardAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfArtistAuthorized($user);

        $currentProjects = $em->getRepository('XBundle:Project')->getCurrentProjects($user);
        $passedProjects = $em->getRepository('XBundle:Project')->getPassedProjects($user);

        return $this->render('@X/XArtist/dashboard_artist.html.twig', [
            'current_projects' => $currentProjects,
            'passed_projects' => $passedProjects
        ]);
    }


    /**
     * @Route("/project/new", name="x_artist_project_new")
     */
    public function newProjectAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, MailDispatcher $mailDispatcher)
    {
        $this->checkIfArtistAuthorized($user);

        $project = new Project();
        $project->setCreator($user);

        $form = $this->createForm(ProjectType::class, $project, ['creation' => true]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $artist = $form->get('artist')->getData();
            $project->setArtist($artist);

            // add all artist owners to project
            $artistOwners = $em->getRepository('AppBundle:Artist_User')->getArtistOwners($artist->getId());
            foreach($artistOwners as $ao) {
                $project->addHandler($ao->getUser());
            }

            // add Un-Mute admin to project
            /*$adminUsers = $em->getRepository('AppBundle:User')->findUsersWithRoles(['ROLE_SUPER_ADMIN']);
            foreach($adminUsers as $au) {
                $project->addHandler($au);
            }*/

            $em->persist($project);
            $em->flush();

            $message = 'Le projet "' . $project->getTitle() . '" a bien été créée. Il doit maintenant être validé par l\'équipe d\'Un-Mute pour être visible par le public sur Chapots';
            $this->addFlash('x_notice', $message);

            try { 
            	$mailDispatcher->sendAdminNewProject($project); 
            }
            catch(\Exception $e) {
            }

            return $this->redirectToRoute('x_artist_dashboard');
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

        return $this->render('@X/XArtist/passed_projects.html.twig', [
            'projects' => $passedProjects,
        ]);
    }


    /**
     * @Route("/project/{id}/update", name="x_artist_project_update")
     */
    public function updateProjectAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project)
    {
        $this->checkIfArtistAuthorized($user);

        if($project->isPassed()) {
            // addFlash('x_error')
            return $this->redirectToRoute('x_artist_passed_projets');
        }
        
        $form = $this->createForm(ProjectType::class, $project, ['is_edit' => true]);
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
     * @Route("/project/{id}/donations-sales-details", name="x_artist_donations_sales_details")
     */
    public function donationsSalesDetailsAction(EntityManagerInterface $em, UserInterface $user = null, Project $project)
    {
        $this->checkIfArtistAuthorized($user);

        //$carts = $em->getRepository('XBundle:XCart')->getProjectCarts($project);

        return $this->render('@X/XArtist/donations_sales_details.html.twig', array(
            'project' => $project
        ));
    }


    /**
     * @Route("/project/{id}/products", name="x_artist_project_products")
     */
    public function viewProductsAction(EntityManagerInterface $em, UserInterface $user = null, Project $project)
    {
        $this->checkIfArtistAuthorized($user);

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
        $this->checkIfArtistAuthorized($user);
        
        $product = new Product();
        
        $form = $this->createForm(ProductType::class, $product, ['creation' => true]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $product->setProject($project);
            $em->persist($product);
            $em->flush();

            $this->addFlash('x_notice', 'La mise en vente de l\'article "' . $product->getName() . '" a bien été enregistrée! Elle doit maintenant être validé par l\'équipe d\'Un-Mute');
            
            try { 
            	$mailDispatcher->sendAdminNewProduct($product); 
            }
            catch(\Exception $e) {
            }
            
            return $this->redirectToRoute('x_artist_project_products', ['id' => $project->getId()]);
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
        $this->checkIfArtistAuthorized($user);

        $product = $em->getRepository('XBundle:Product')->find($idProd);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            $this->addFlash('x_notice', 'L\'article a bien été modifié.');
            return $this->redirectToRoute('x_artist_project_products', ['id' => $project->getId()]);
        }

        return $this->render('@X/XArtist/Product/product_add.html.twig', array(
            'form' => $form->createView(),
            'project' => $project,
            'product' => $product
        ));
    }



    /**
     * @Route("/project/{id}/ticket/add", name="x_artist_ticket_add")
     */
    public function addTicketAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project)
    {
        $this->checkIfArtistAuthorized($user);

        // check if project = concert

        return $this->render('@X/XArtist/Product/ticket_add.html.twig', array(
            'project' => $project
        ));
    }


    /**
     * @Route("/project/{id}/{code}remove-photo", name="x_artist_project_remove_photo")
     */
    public function removePhotoAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, Project $project, $code) {

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