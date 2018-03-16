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
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        //TODO ChangeQuerry Minimum 1 Achat
        $user_stat = null;
        $user_category = null;
        foreach ($users as $user) {
            $this->formulaParserService->setUserStatisticVariables($user->getId());
            foreach ($categories as $category) {
                $levels = $category->getLevels()->toArray();
                $user_stat = $this->formulaParserService->computeStatistic($category->getFormula());
                if ($user_stat == 0) {
                    continue;
                } else {
                    $user_category = $this->em->getRepository('AppBundle:User_Category')->findOneBy(array('category' => $category, 'user' => $user));
                    if ($user_category == null) {
                        $user_category = new User_Category();
                        $user_category->setUser($user);
                        $user_category->setCategory($category);
                    }else if($user_category->getStatistic() == $user_stat){
                        continue;
                    }
                    $user_category->setStatistic($user_stat);
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
}