<?php

namespace AppBundle\Controller;

use AppBundle\Services\MailDispatcher;
use AppBundle\Services\RankingService;
use AppBundle\Services\RewardAttributionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RankingAdminController extends BaseAdminController
{
    /**
     * list all ranking
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $rankingservice = $this->container->get('AppBundle\Services\RankingService');
        $categories = [];
        $maximums = [];
        $rewardAttributionService = $this->container->get('AppBundle\Services\RewardAttributionService');
        $formula_descriptions = [];
        try {
            $em = $this->getDoctrine()->getManager();
            $categories = $em->getRepository('AppBundle:Category')->findForRaking();
            $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
            $rewardAttributionService->limitStatistics($categories);
            $formula_descriptions = $rankingservice->buildFormulaDescription($categories);
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
    public function computeAction(Request $request, RewardAttributionService $rewardAttributionService, RankingService $rankingService)
    {
        try {
            $rankingService->computeAllStatistic();
        } catch (\Exception $ex) {
            $this->addFlash('notice', 'Exception : ' . $ex->getMessage());
            $this->logger->warning("Exception computeAction", [$ex->getMessage()]);
        } finally {
            $em = $this->getDoctrine()->getManager();
            $categories = $em->getRepository('AppBundle:Category')->findForRaking();
            $maximums = $em->getRepository('AppBundle:Level')->countMaximums();
            $rewardAttributionService->limitStatistics($categories);
            $formula_descriptions = $rankingService->buildFormulaDescription($categories);
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
        try {
            $em = $this->getDoctrine()->getManager();
            $statistics = $em->getRepository('AppBundle:User_Category')->findStatLimit($request->get('level_id'), $request->get('limit'));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
        return $this->render('@App/Admin/Ranking/ranking_table_preview.html.twig', array(
            'statistics' => $statistics
        ));
    }

    public function displayModalAction(Request $request, RewardAttributionService $rewardAttributionService)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $checkedStatistics = $request->get('stats');
            if ($request->get('route') == "displayEmailModal") {
                $template = '@App/Admin/ranking/send_email_preview.html.twig';
            } else {
                $template = '@App/Admin/ranking/give_reward_preview.html.twig';
            }
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $stats = $rewardAttributionService->getSelectedStats($checkedStatistics, $allStatistics);
            $rewards = $rewardAttributionService->constructRewardSelectWithType($request->getLocale());
            return $this->render($template, array('recipients' => $stats, 'level_id' => $request->get('level'), 'rewards' => $rewards));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }

    public function sendEmailAction(Request $request, MailDispatcher $mailDispatcher, RewardAttributionService $rewardAttributionService)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $checkedStatistics = $request->get('stats');
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $stats = $rewardAttributionService->getSelectedStats($checkedStatistics, $allStatistics);
            $mailDispatcher->sendRankingEmail($stats, $request->get('mailObject'), $request->get('mailContent'));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
        return new Response("Email(s) envoyés(s)", 200, []);
    }

    public function giveRewardAction(Request $request, RewardAttributionService $rewardAttributionService)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $checkedStatistics = $request->get('stats');
            $allStatistics = $em->getRepository('AppBundle:User_Category')->findAllStatByLevel($request->get('level'));
            $stats = $rewardAttributionService->getSelectedStats($checkedStatistics, $allStatistics);
            $reward = $em->getRepository('AppBundle:Reward')->getReward(intval($request->get('reward')));
            $rewardAttributionService->giveReward($stats, $reward, $request->get('notification'), $request->get('email'), $request->get('emailContent'));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);

        }
        return new Response("Récompense(s) attribuée(s)", 200, []);

    }

}