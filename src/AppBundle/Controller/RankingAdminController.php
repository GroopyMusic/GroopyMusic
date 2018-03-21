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
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RankingAdminController extends Controller
{
    protected $container;
    private $rankingervice;
    private $logger;

    public function __construct(ContainerInterface $container, RankingService $rankingService, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->configure();
        $this->rankingervice = $rankingService;
        $this->logger = $logger;
    }

    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findForRaking();
        $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
        $this->limitStatistics($categories);
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories,
            'maximums' => $maximums
        ));
    }

    public function computeAction(Request $request)
    {
        $this->rankingervice->computeAllStatistic();
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findForRaking();
        $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
        $this->limitStatistics($categories);
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories,
            'maximums' => $maximums
        ));
    }

    public function displayMoreAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $statistics = $em->getRepository('AppBundle:User_Category')->findStatLimit($request->get('level_id'), $request->get('limit'));
        return $this->render('@App/Admin/ranking/ranking_table_preview.html.twig', array(
            'statistics' => $statistics
        ));
    }


    /**
     * get only the first 5 lines of each category level
     *
     * @param $categories
     *
     */
    private function limitStatistics($categories)
    {
        foreach ($categories as $category) {
            foreach ($category->getLevels()->toArray() as $level) {
                $level->setStatistics(array_slice($level->getStatistics()->toArray(), 0, 5, true));
            }
        }
    }
}