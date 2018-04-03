<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 13/03/2018
 * Time: 12:10
 */

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Reward;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Category;
use AppBundle\Entity\User_Reward;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\RankingService;
use AppBundle\Services\RewardAttributionService;
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
            $this->rankingervice->computeAllStatistic();
        } catch (\Exception $ex) {
            $this->addFlash('notice', 'Exception : ' . $ex->getMessage());
            $this->logger->warning("Exception computeAction", [$ex->getMessage()]);
        } finally {
            $em = $this->getDoctrine()->getManager();
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
        if ($request->get('route') == "displayEmailModal") {
            $template = '@App/Admin/ranking/send_email_preview.html.twig';
        } else {
            $template = '@App/Admin/ranking/give_reward_preview.html.twig';
        }
        if ($checkedStatistics == null) {
            return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'warning'));
        }
        $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
        $stats = $this->getSelectedStats($checkedStatistics, $allStatistics);
        $rewards = $em->getRepository('AppBundle:Reward')->findNotDeletedRewards();
        return $this->render($template, array('recipients' => $stats, 'level_id' => $request->get('level'), 'rewards' => $rewards));
    }

    public function sendEmailAction(Request $request, MailDispatcher $mailDispatcher)
    {
        $em = $this->getDoctrine()->getManager();
        $checkedStatistics = $request->get('stats');
        $template = '@App/Admin/ranking/send_email_preview.html.twig';
        if ($checkedStatistics == null) {
            return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'warning'));
        }
        try {
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $stats = $this->getSelectedStats($checkedStatistics, $allStatistics);
            $mailDispatcher->sendRankingEmail($stats, $request->get('mailObject'), $request->get('mailContent'));
        } catch (\Exception $ex) {
            return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'error', 'exception' => $ex->getMessage()));
        }
        return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'success'));
    }

    public function giveRewardAction(Request $request, RewardAttributionService $rewardAttributionService)
    {
        $em = $this->getDoctrine()->getManager();
        $checkedStatistics = $request->get('stats');
        $template = '@App/Admin/ranking/give_reward_preview.html.twig';
        if ($checkedStatistics == null) {
            return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'warning'));
        }
        try {
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $stats = $this->getSelectedStats($checkedStatistics, $allStatistics);
            $reward = $em->getRepository('AppBundle:Reward')->getReward(intval($request->get('reward')));
            $rewardAttributionService->giveReward($stats, $reward, $request->get('notification'), $request->get('email'), $request->get('emailContent'));
        } catch (\Exception $ex) {
            return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'error', 'exception' => $ex->getMessage()));
        }
        return $this->render($template, array('recipients' => [], 'level_id' => $request->get('level'), 'message' => 'success'));

    }

    private function getSelectedStats($ids, $allStatistics)
    {
        $users = [];
        foreach ($ids as $id) {
            array_push($users, $allStatistics[$id]);
        }
        return $users;
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