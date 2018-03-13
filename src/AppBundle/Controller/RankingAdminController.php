<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 13/03/2018
 * Time: 12:10
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class RankingAdminController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->configure();
    }

    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig',array(
            'categories' => $categories
        ));
    }
}