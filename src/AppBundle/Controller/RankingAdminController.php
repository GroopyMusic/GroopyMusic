<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 13/03/2018
 * Time: 12:10
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\User;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\RankingService;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RankingAdminController extends Controller
{
    protected $container;
    private $rankingervice;
    private $logger;

    /**
     * RankingAdminController constructor.
     * @param ContainerInterface $container
     * @param RankingService $rankingService
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, RankingService $rankingService, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->configure();
        $this->rankingervice = $rankingService;
        $this->logger = $logger;
    }

    /**
     * list all ranking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $categories = [];
        $maximums = [];
        $formula_descriptions = [];
        try {
            $em = $this->getDoctrine()->getManager();
            $categories = $em->getRepository('AppBundle:Category')->findForRaking();
            $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
            $this->limitStatistics($categories);
            $formula_descriptions = $this->rankingervice->buildFormulaDescription($categories);
        } catch (\Exception $ex) {
            $this->addFlash('notice', 'Exception :' . $ex->getMessage());
            $this->logger->warning("Exception listAction", [$ex->getMessage()]);
        }
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories,
            'maximums' => $maximums,
            'formula_desc' => $formula_descriptions

        ));
    }

    /**
     * compute all statistics and list ranking
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function computeAction(Request $request)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $this->rankingervice->computeAllStatistic();
        } catch (\Exception $ex) {
            $this->addFlash('notice', 'Exception : ' . $ex->getMessage());
            $this->logger->warning("Exception computeAction", [$ex->getMessage()]);
        } finally {
            $categories = $em->getRepository('AppBundle:Category')->findForRaking();
            $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
            $this->limitStatistics($categories);
            $formula_descriptions = $this->rankingervice->buildFormulaDescription($categories);
        }
        return $this->render('@App/Admin/Ranking/ranking_view.html.twig', array(
            'categories' => $categories,
            'maximums' => $maximums,
            'formula_desc' => $formula_descriptions
        ));
    }


    /**
     * Retrieves and displays 5 additional statistics of a level
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayMoreAction(Request $request)
    {
        $statistics = [];
        try {
            $em = $this->getDoctrine()->getManager();
            $statistics = $em->getRepository('AppBundle:User_Category')->findStatLimit($request->get('level_id'), $request->get('limit'));
        } catch (\Exception $ex) {
            $this->addFlash('notice', 'Exception :' . $ex->getMessage());
            $this->logger->warning("Exception displayMoreAction", [$ex->getMessage()]);
        }
        return $this->render('@App/Admin/ranking/ranking_table_preview.html.twig', array(
            'statistics' => $statistics
        ));
    }

    public function displayModalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $checkedStatistics = $request->get('stats');
        $template = '@App/Admin/ranking/send_email_preview.html.twig';
        if ($checkedStatistics == null) {
            return $this->render($template, array(
                'recipients' => [],
                'level_id' => $request->get('level'),
                'message' => 'warning'
            ));
        }
        $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
        $users = $this->getSelectedUsers($checkedStatistics, $allStatistics);
        return $this->render($template, array(
            'recipients' => $users,
            'level_id' => $request->get('level')
        ));
    }

    public
    function sendEmailAction(Request $request, MailDispatcher $mailDispatcher)
    {
        $em = $this->getDoctrine()->getManager();
        $checkedStatistics = $request->get('stats');
        $template = '@App/Admin/ranking/send_email_preview.html.twig';
        if ($checkedStatistics == null) {
            return $this->render($template, array(
                'recipients' => [],
                'level_id' => $request->get('level'),
                'message' => 'warning'
            ));
        }
        try {
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $users = $this->getSelectedUsers($checkedStatistics, $allStatistics);
            $mailDispatcher->sendRankingEmail($users, $request->get('mailObject'), $request->get('mailContent'));
        } catch (\Exception $ex) {
            return $this->render($template, array(
                'recipients' => [],
                'level_id' => $request->get('level'),
                'message' => 'error',
                'exception' => $ex->getMessage()
            ));
        }
        return $this->render($template, array(
            'recipients' => [],
            'level_id' => $request->get('level'),
            'message' => 'success'
        ));
    }

    private
    function getSelectedUsers($ids, $allStatistics)
    {
        $users = [];
        foreach ($ids as $id) {
            array_push($users, $allStatistics[$id]->getUser());
        }
        return $users;
    }


    /**
     * get only the first 5 lines of each category level
     *
     * @param $categories
     *
     */
    private
    function limitStatistics($categories)
    {
        foreach ($categories as $category) {
            foreach ($category->getLevels()->toArray() as $level) {
                $level->setStatistics(array_slice($level->getStatistics()->toArray(), 0, 5, true));
            }
        }
    }
}