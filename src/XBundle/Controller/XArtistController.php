<?php

namespace XBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Services\MailDispatcher;
use Doctrine\ORM\EntityManagerInterface;
//use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
    public function newProjectAction(EntityManagerInterface $em, UserInterface $user = null, Request $request/*, MailDispatcher $mailDispatcher*/)
    {
        $project = new Project();
        $project->setUser($user);

    $form = $this->createForm(ProjectType::class, $project, ['creation' => true]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $artist = $form->get('artist')->getData();
            $project->setArtist($artist);
            $em->persist($project);
            $em->flush();

            //$this->addFlash('x_notice', 'Le projet a bien été créée.');

            $request->getSession()->getFlashBag()->add('x_notice', 'Le projet a été créé');

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
        $passedProjects = $em->getRepository('XBundle:Project')->getPassedProjects($user);

        return $this->render('@X/XArtist/passed_projects.html.twig', [
            'projects' => $passedProjects,
        ]);
    }


    /**
     * @Route("/project/{id}/update", name="x_artist_project_update")
     */
    public function updateProjectAction(EntityManagerInterface $em, $id)
    {

        $project = $em->getRepository('XBundle:Project')->find($id);
        $form = $this->createForm(ProjectType::class, $project/*, ['creation' => true]*/);

        return $this->render('@X/XArtist/project_new.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/project/{id}/donations-sales-details", name="x_artist_donations_sales_details")
     */
    public function donationsSalesDetailsAction(EntityManagerInterface $em, $id)
    {
        //$project = $em->getRepository('XBundle:Project')->find($id);
        //$carts = $em->getRepository('XBundle:XCart')->getProjectCarts($project);

        //file_put_contents('test.txt', $carts[0]);

        return $this->render('@X/XArtist/donations_sales_details.html.twig'/*, 
                                array()*/);
    }


    /**
     * @Route("/project/{id}/products", name="x_artist_project_products")
     */
    public function viewProductsAction(EntityManagerInterface $em, $id)
    {
        $project = $em->getRepository('XBundle:Project')->find($id);
        $products = $em->getRepository('XBundle:Product')->getProjectProducts($project);
        return $this->render('@X/XArtist/Product/products.html.twig', array(
            'project' => $project,
            'products' => $products
        ));
    }


    /**
     * @Route("/project/{id}/product/new", name="x_artist_product_new")
     */
    public function newProductAction(EntityManagerInterface $em, Request $request, $id)
    {
        $product = new Product();

        $project = $em->getRepository('XBundle:Project')->find($id);
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $product->setProject($project);
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('x_artist_project_products', ['id' => $id]);
        }

        return $this->render('@X/XArtist/Product/product_new.html.twig', array(
            'project' => $project,
            'form' => $form->createView()
        ));
    }

}

?>