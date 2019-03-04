<?php
// src/XBundle/Controller/ProductController.php
namespace XBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use XBundle\Entity\Product;
use XBundle\Controller\Projects;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use XBundle\Form\Type\ProductType;
use XBundle\Form\Type\ProductEditType;


class ProductController extends Controller
{

  public function addAction($projectId, Request $request)
  {
    $project = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->find($projectId);
    $product = new Product();
  
    $form = $this->get('form.factory')->create(ProductType::class, $product);
    $product->setProject($project);
    
    if($request->isMethod('POST')) {
      $form->handleRequest($request);
      
      if($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();
        
        $request->getSession()->getFlashBag()->add('notice', 'Produit enregistré');
        
        return $this->redirectToRoute('x_user_viewProjects');
      }
    }
      return $this->render('XBundle:Product:add_product.html.twig', array(
        'project' => $project,
        'form' => $form->createView(),
      ));
  }

  //Fonctionnalités à implémenter
  public function editAction($id, Request $request)
  {
    
  }
  public function deleteAction(Request $request, $id)
  {
    
  }
  
}