<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/03/2018
 * Time: 16:44
 */

namespace AppBundle\Services;

use AppBundle\Entity\User_Category;
use AppBundle\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RankingService
{
    private $formulaParserService;

    private $em;

    private $logger;

    public function __construct(FormulaParserService $formulaParserService, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->formulaParserService = $formulaParserService;
        $this->em = $em;
        $this->logger = $logger;

    }

    public function computeAllStatistic()
    {
        $categories = $this->em->getRepository('AppBundle:Category')->findLevelsByCategories();
        $users = $this->em->getRepository('AppBundle:User')->findUsersNotDeleted();
        $statistics = $this->em->getRepository('AppBundle:User')->countUsersStatistic();
        $point = null;
        $user_category = null;
        foreach ($users as $user) {
            if (!array_key_exists($user->getId(), $statistics)) {
                continue;
            } else {
                $this->formulaParserService->setUserStatisticsVariables($statistics[$user->getId()]);
            }
            foreach ($categories as $category) {
                $levels = $category->getLevels()->toArray();
                $point = $this->formulaParserService->computeStatistic($category->getFormula());
                if ($point == 0) {
                    continue;
                } else {
                    $user_category = $this->getCategory($user->getCategoryStatistics()->toArray(), $category->getId());
                    $this->logger->warning("rep", [$user_category]);
                    if ($user_category == null) {
                        $user_category = new User_Category();
                        $user_category->setUser($user);
                        $user_category->setCategory($category);
                    } else if ($user_category->getStatistic() == $point) {
                        continue;
                    }
                    $user_category->setStatistic($point);
                    $user_category = $this->checkLevel($user_category, $levels);
                    $this->em->persist($user_category);
                }
            }
        }
        $this->em->flush();
    }

    private function checkLevel($user_category, $levels)
    {
        $statistic = $user_category->getStatistic();
        $correctLevel = null;
        $level_step = null;
        foreach ($levels as $level) {
            $level_step = $level->getStep();
            if ($level_step <= $statistic) {
                if ($correctLevel == null || $level_step > $correctLevel->getStep()) {
                    $correctLevel = $level;
                }
            }
        }
        return $user_category->setLevel($correctLevel);
    }

    private function getCategory($stats, $id)
    {
        $this->logger->warning("cat", [$stats, $id]);
        foreach ($stats as $stat) {
            if ($stat->getCategory()->getId() === $id) {
                return $stat;
            }
        }
        return null;
    }
}