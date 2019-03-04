<?php
// src/XBundle/Controller/ProjectsController.php
namespace XBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use XBundle\Entity\Projects;
use XBundle\Entity\Product;
use XBundle\Entity\XCart;
use XBundle\Entity\Points;
use AppBundle\Entity\User;
use AppBundle\Entity\Artist;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use XBundle\Form\Type\ProjectsType;
use XBundle\Form\Type\DonationType;
use XBundle\Form\Type\BuyProductType;
use XBundle\Form\Type\ProjectsEditType;


class ProjectsController extends Controller
{
  public function indexAction(){
	  $listProjects = $this->getDoctrine()
		->getManager()
		->getRepository('XBundle:Projects')
		->findBy(array(), array('points' => 'desc'))
		; //findAll est peut-être mieux - pas besoin de les afficher seulement le nombre de points

	  return $this->render('XBundle:Projects:index.html.twig', array(
		'listProjects' => $listProjects
		));
  }

  //Page d'accueil avec le projet à l'affiche
  public function homeAction(){
    $projects = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->findAll();

    $points = array();

    //Trouver le projet qui a le plus de points 
    foreach($projects as $project){
      $points[] = $project->getPoints();

    }

    $maxPoints = max($points);

    $chosenProject = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->findOneBy(array("points" => $maxPoints));

    return $this->render('XBundle:Projects:home.html.twig', array(
      'project' => $chosenProject));
  }

  public function viewAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
	  $project = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->find($id);
    $listProduct = $this->getDoctrine()->getManager()->getRepository('XBundle:Product')->findAll();

    $formDonation = $this->get('form.factory')->create(DonationType::class);
    $formDonation->handleRequest($request);


    //Création d'un panier lorsque l'utilisateur valide le formulaire de donation
    if($formDonation->isSubmitted())
    {
      $cart = new XCart();
      $cart->setConfirmed(true);
      $cart->setPaid(false);
      $cart->setDonationAmount($formDonation["donation_amount"]->getData());
      $cart->setProjects($project);
      $cart->generateBarCode();

      $em->persist($cart);
      $em->flush();

      return $this->redirectToRoute('x_payment', ['code' => $cart->getBarcodeText()]);
    }



	  
	  return $this->render('XBundle:Projects:view.html.twig', array(
	    'project' => $project,
      'listProduct' => $listProduct,
      'formDonation' => $formDonation->createView() ));
    
  }
  public function orderProductAjaxAction($id, Request $request)
  {
        $project = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->find($id);
        $product = $this->getDoctrine()->getManager()->getRepository('XBundle:Product')->find($request->request->get('productId'));
        $em = $this->getDoctrine()->getManager();
        $cart = new XCart();
        $cart->setConfirmed(true);
        $cart->setPaid(false);
        $cart->setProductQuantity($request->request->get('quantity'));
        $cart->setProduct($product);
        $cart->setProjects($project);
        $cart->generateBarCode();

        $em->persist($cart);
        $em->flush();

        $response = new Response(json_encode(array(
          'redirect' => $this->generateUrl('x_payment', array('code' => $cart->getBarcodeText())),
          'quantity' => $request->request->get('quantity'),
          'productId' => $request->request->get('productId'))));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
  }
  public function addAction(Request $request)
  {
    $project = new Projects();
    $points = new Points();
    $artist = new Artist();
	  $user = $this->container->get('security.token_storage')->getToken()->getUser();
	  $form = $this->get('form.factory')->create(ProjectsType::class, $project);
	
  	if($request->isMethod('POST')) {
  		$form->handleRequest($request);
  		
  		if($form->isValid()) {
  			$em = $this->getDoctrine()->getManager();
        $points->setProjectId($project);
        $points->setGavePoints(0);
        $project->setPoints(0);
        $project->setUser($user);
  			$em->persist($project);
        $em->persist($points);
  			$em->flush();
  			
  			$request->getSession()->getFlashBag()->add('notice', 'Projet enregistré');
  			
  			return $this->redirectToRoute('x_projects_home', array('id' => $project->getId()));
  		}
	}
	
	return $this->render('XBundle:Projects:add.html.twig', array(
		'form' => $form->createView(),
		));
  }

  //Fonctionalité non terminée 
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

  //FOnctionnalité non terminée
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

  //Ajax call pour l'ajout de points aux projets
  public function ajaxAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $user = $this->container->get('security.token_storage')->getToken()->getUser();
    $project = $em->getRepository('XBundle:Projects')->find($request->request->get('projectId'));
    $projectId = $request->request->get('projectId');
    $pointsGiven = $em->getRepository('XBundle:Points')->findOneBy(array('project_id' => $projectId));

    if( $em->getRepository('XBundle:Points')->findOneBy(array('user_id' => $user->getId(), 'project_id' => $projectId)) == null){

      $pointsGiven = new Points();
      $pointsGiven->setUserId($user);
      $pointsGiven->setProjectId($project);
      $pointsGiven->setGavePoints(1);
      $project->setPoints($request->request->get('points') + 1);

      $em->persist($pointsGiven);
      $em->flush();

    }
    else if($em->getRepository('XBundle:Points')->findOneBy(array('user_id' => $user->getId(), 'project_id' => $projectId, 'gavePoints' => 0)))
    {
      $pointsGiven->setUserId($user);
      $pointsGiven->setProjectId($project);
      $pointsGiven->setGavePoints(1);
      $project->setPoints($request->request->get('points') + 1);

      $em->persist($pointsGiven);
      $em->flush();
    }
    else{
      $pointsGiven->setUserId($user);
      $pointsGiven->setProjectId($project);
      $pointsGiven->setGavePoints(0);
      $project->setPoints($request->request->get('points') - 1);

      $em->persist($pointsGiven);
      $em->flush();
    }

    $newPoints = $project->getPoints();



    $response = new Response(json_encode(array(
      'points' => $newPoints,
      'projectId' => $projectId)));

    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
  
}