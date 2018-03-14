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

class RankingAdminController extends Controller
{
    protected $container;
    private $rankingervice;
    public function __construct(ContainerInterface $container,RankingService $rankingService)
    {
        $this->container = $container;
        $this->configure();
        $this->rankingervice = $rankingService;
    }

    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findForRaking();
        //$this->rankingervice->sortLevelDesc($categories[0]->getLevels()->getData());
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig',array(
            'categories' => $categories
        ));
    }
}