<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 13/03/2018
 * Time: 12:10
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Services\RankingService;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RankingAdminController extends Controller
{
    protected $container;
    private $rankingervice;

    public function __construct(ContainerInterface $container, RankingService $rankingService)
    {
        $this->container = $container;
        $this->configure();
        $this->rankingervice = $rankingService;
    }

    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findForRaking();
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories
        ));
    }

    public function computeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->rankingervice->computeAllStatistic();
        $categories = $em->getRepository('AppBundle:Category')->findForRaking();
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories
        ));
    }
}