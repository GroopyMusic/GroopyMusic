<?php

namespace XBundle\Controller;

use AppBundle\Entity\Artist;
//use AppBundle\Entity\Projects;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

// Contrôleur servant à affichant les projets et les produits à l'utilisateur
class UserController extends Controller
{
	public function viewProjectsAction(Request $request){
		$listProjects = $this->getDoctrine()->getManager()->getRepository('XBundle:Projects')->findAll();
		$listProducts = $this->getDoctrine()->getManager()->getRepository('XBundle:Product')->findAll();
		
		return $this->render('XBundle:User:myprojects.html.twig', array(
			'listProjects' => $listProjects,
			'listProducts' => $listProducts,
			 ));


	}
}
		