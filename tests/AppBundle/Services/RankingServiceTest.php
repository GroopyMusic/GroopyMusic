<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 09/05/2018
 * Time: 15:59
 */

use AppBundle\Entity\Category;
use AppBundle\Entity\Level;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Category;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\UserRepository;
use AppBundle\Services\FormulaParserService;
use AppBundle\Services\RankingService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RankingServiceTest extends TestCase
{
    //MOCK
    private $rankingService;
    private $logger;
    private $manager;
    private $formulaParser;
    private $categoryRepository;
    private $userRepository;
    private $user1;
    private $category1;
    private $category2;
    private $level1;
    private $level2;
    private $user_category1;
    private $user_category2;

    protected function setUp()
    {
        //repository
        $this->categoryRepository = $this->getMockBuilder(CategoryRepository::class)->disableOriginalConstructor()->getMock();
        $this->userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        //entity
        $this->user1 = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $this->user1->expects($this->any())->method('getId')->willReturn(1);
        $this->category1 = $this->getMockBuilder(Category::class)->disableOriginalConstructor()->getMock();
        $this->level1 = $this->getMockBuilder(Level::class)->disableOriginalConstructor()->getMock();
        $this->user_category1 = $this->getMockBuilder(User_Category::class)->disableOriginalConstructor()->getMock();

        //service
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $repositories = array(
            array('AppBundle:Category', $this->categoryRepository),
            array('AppBundle:User', $this->userRepository),
        );
        $this->manager->expects($this->any())->method('getRepository')->will($this->returnValueMap($repositories));

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->formulaParser = $this->getMockBuilder(FormulaParserService::class)
            ->setMethods(array('setUserStatisticsVariables', 'computeStatistic'))
            ->disableOriginalConstructor()
            ->getMock();

        //test
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger, $this->logger))
            ->setMethods(array('deleteStatistic', 'mergeStatistics', 'getUserCategory', 'checkLevel'))
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->rankingService);
        unset($this->formulaParser);
        unset($this->logger);
        unset($this->categoryRepository);
        unset($this->userRepository);
        unset($this->user1);
        unset($this->category1);
        unset($this->level1);
        unset($this->user_category1);
    }

    /**
     * Success calcul point of one user
     */
    public function testComputeAllStatistic1()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([1 => []]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category1);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->level1);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(1);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test Users empty
     */
    public function testComputeAllStatistic2()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([1 => []]);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test users == null
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testComputeAllStatistic3()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn(null);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test Categories Empty
     */
    public function testComputeAllStatistic4()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([1 => []]);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(1);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * @expectedException Exception
     * test Categories null
     */
    public function testComputeAllStatistic5()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn(null);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([1 => []]);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test satistics empty
     */
    public function testComputeAllStatistic6()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn([]);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * @expectedException Exception
     * test satistics null
     */
    public function testComputeAllStatistic7()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn(null);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * @expectedException Exception
     * test satistics null
     */
    public function testComputeAllStatistic8()
    {
        $this->user1->expects($this->any())->method('getCategoryStatistics')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->user_category1]));
        $this->category1->expects($this->any())->method('getLevels')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([$this->level1]));
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category1]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user1]);
        $this->rankingService->expects($this->any())->method('mergeStatistics')->willReturn(null);
        $this->logger->expects($this->any())->method('warning')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }
}
