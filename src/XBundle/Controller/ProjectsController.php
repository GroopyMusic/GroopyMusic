<?php
// src/XBundle/Controller/ProjectsController.php
namespace XBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use XBundle\Entity\Projects;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use XBundle\Form\ProjectsType;
use XBundle\Form\ProjectsEditType;


class ProjectsController extends Controller
{
  public function indexAction(){
	  $listProjects = $this->getDoctrine()
		->getManager()
		->getRepository('XBundle:Projects')
		->findAll()
		;
		
	  return $this->render('XBundle:Projects:index.html.twig', array(
		'listProjects' => $listProjects,
		));
  }
  public function viewAction($id)
  {
    
  }
  public function addAction(Request $request)
  {
    $project = new Projects();
	
	$form = $this->get('form.factory')->create(ProjectsType::class, $project);
	
	if($request->isMethod('POST')) {
		$form->handleRequest($request);
		
		if($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($project);
			$em->flush();
			
			$request->getSession()->getFlashBag()->add('notice', 'Projet enregistré');
			
			return $this->redirectToRoute('x_projects_home', array('id' => $project->getId()));
		}
	}
	
	return $this->render('XBundle:Projects:add.html.twig', array(
		'form' => $form->createView(),
		));
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $project = $em->getRepository('XBundle:Projects')->find($id);

    if (null === $project) {
      throw new NotFoundHttpException("Le projet d'id ".$id." n'existe pas.");
    }

    $form = $this->get('form.factory')->create(ProjectsEditType::class, $project);

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('x_projects_view', array('id' => $project->getId()));
    }

    return $this->render('XBundle:Projects:edit.html.twig', array(
      'project' => $project,
      'form'   => $form->createView(),
    ));
  }
  public function deleteAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $project = $em->getRepository('XBundle:Projects')->find($id);

    if (null === $project) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
    // Cela permet de protéger la suppression d'annonce contre cette faille
    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($project);
      $em->flush();

      $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

      return $this->redirectToRoute('x_projects_home');
    }
    
    return $this->render('XBundle:Projects:delete.html.twig', array(
      'project' => $project,
      'form'   => $form->createView(),
    ));
  }
  
}