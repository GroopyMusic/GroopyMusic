<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/03/2018
 * Time: 16:44
 */

namespace AppBundle\Services;

use AppBundle\Entity\User_Category;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    /**
     * compute and update statistics for all users
     */
    public function computeAllStatistic()
    {
        $point = null;
        $exceptions = 0;
        $user_category = null;
        $last_exception_message = null;

        $categories = $this->em->getRepository('AppBundle:Category')->findLevelsByCategories();
        $users = $this->em->getRepository('AppBundle:User')->findUsersNotDeleted();
        $statistics = $this->em->getRepository('AppBundle:User')->countUsersStatistic();

        foreach ($users as $user) {
            if ($exceptions > 5) {
                $this->logger->warning("Number of exceptions > 5", []);
                throw new Exception($last_exception_message);
            }
            try {
                if (!array_key_exists($user->getId(), $statistics)) {
                    $this->deleteStatistic($user);
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
                        $user_category = $this->getUserCategory($user->getCategoryStatistics()->toArray(), $category->getId());
                        if ($user_category == null) {
                            $user_category = new User_Category();
                            $user_category->setUser($user);
                            $user_category->setCategory($category);
                        } else if ($user_category->getStatistic() == $point) {
                            $user_category = $this->checkLevel($user_category, $levels);
                            $this->em->persist($user_category);
                            continue;
                        }
                        $user_category->setStatistic($point);
                        $user_category = $this->checkLevel($user_category, $levels);
                        $this->em->persist($user_category);
                    }
                }
            } catch (\Exception $ex) {
                $exceptions++;
                $last_exception_message = $ex->getMessage();
                $this->logger->warning("Exception compute stat user", [$last_exception_message]);
                continue;
            }
        }
        $this->em->flush();
    }

    /**
     * find and set the most appropriate level for $user_category amoung levels of a category
     *
     * @param $user_category
     * @param $levels
     * @return $user_category with correct level
     */
    private function checkLevel($user_category, $levels)
    {
        $statistic = $user_category->getStatistic();
        $correct_level = null;
        $level_step = null;
        foreach ($levels as $level) {
            $level_step = $level->getStep();
            if ($level_step <= $statistic) {
                if ($correct_level == null || $level_step > $correct_level->getStep()) {
                    $correct_level = $level;
                }
            }
        }
        return $user_category->setLevel($correct_level);
    }

    /**
     * get the user_category that matches with the category id among the statistics of a user
     *
     * @param $stats
     * @param $id
     * @return $user_category if exist , null if not
     */
    private function getUserCategory($stats, $id)
    {
        foreach ($stats as $stat) {
            if ($stat->getCategory()->getId() === $id) {
                return $stat;
            }
        }
        return null;
    }

    /**
     * remove the user_categories of $user
     *
     * @param $user
     *
     */
    private function deleteStatistic($user)
    {
        foreach ($user->getCategoryStatistics() as $stat) {
            $this->em->remove($stat);
        }
    }

    /**
     * build description of each category formula
     *
     * @param $categories
     * @return array | key => category_id | value => formula_descriptions
     */
    public function buildFormulaDescription($categories)
    {
        $formula_descriptions = [];
        foreach ($categories as $category) {
            $formula_descriptions[$category->getId()] = strtr($category->getFormula(), $this->formulaParserService->querry_descritpions);
        }
        return $formula_descriptions;
    }
}