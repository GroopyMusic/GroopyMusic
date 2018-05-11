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
use AppBundle\Services\ArrayHelperService;
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
    private $arrayHelper;
    private $categoryRepository;
    private $userRepository;
    private $user;
    private $category;
    private $level1;
    private $level2;
    private $user_category;

    protected function setUp()
    {
        //repository
        $this->categoryRepository = $this->getMockBuilder(CategoryRepository::class)->disableOriginalConstructor()->getMock();
        $this->userRepository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();

        //entity
        $this->user = new User();
        $this->category = new Category();
        $this->level1 = new Level();
        $this->level2 = new Level();
        $this->user_category = new User_Category();

        //service
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $this->manager->expects($this->any())->method('remove');
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
        $this->arrayHelper = $this->getMockBuilder(ArrayHelperService::class)
            ->setMethods(array('mergeMapOfArray'))
            ->disableOriginalConstructor()
            ->getMock();

        //test
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(array('deleteStatistics', 'getUserCategory', 'checkLevel'))
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->rankingService);
        unset($this->formulaParser);
        unset($this->logger);
        unset($this->categoryRepository);
        unset($this->userRepository);
        unset($this->user);
        unset($this->category);
        unset($this->level1);
        unset($this->level2);
        unset($this->user_category);
        unset($this->arrayHelper);
    }

    /**
     * Success calcul point of one user
     * with point modification and level modification
     */
    public function testComputeAllStatistic1()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user->addCategoryStatistic($this->user_category);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);
        $this->user_category->setStatistic(3);
        $this->user_category->setLevel($this->level1);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category);
        $this->rankingService->expects($this->any())->method('checkLevel')->willReturn($this->level2);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(5);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals($this->level2, $this->user_category->getLevel());
        $this->assertEquals(5, $this->user_category->getStatistic());
    }

    /**
     * Success calcul point of one user
     * without point modification and without level modification
     */
    public function testComputeAllStatistic2()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user->addCategoryStatistic($this->user_category);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);
        $this->user_category->setStatistic(5);
        $this->user_category->setLevel($this->level1);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category);
        $this->rankingService->expects($this->any())->method('checkLevel')->willReturn($this->level1);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(5);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals($this->level1, $this->user_category->getLevel());
        $this->assertEquals(5, $this->user_category->getStatistic());
    }

    /**
     * Success calcul point of one user
     * without point modification and with level modification
     */
    public function testComputeAllStatistic3()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user->addCategoryStatistic($this->user_category);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);
        $this->user_category->setStatistic(5);
        $this->user_category->setLevel($this->level1);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category);
        $this->rankingService->expects($this->any())->method('checkLevel')->willReturn($this->level2);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(5);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals($this->level2, $this->user_category->getLevel());
        $this->assertEquals(5, $this->user_category->getStatistic());
    }

    /**
     * Success calcul point of one user
     * with point modification and without level modification
     */
    public function testComputeAllStatistic4()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user->addCategoryStatistic($this->user_category);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);
        $this->user_category->setStatistic(3);
        $this->user_category->setLevel($this->level1);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category);
        $this->rankingService->expects($this->any())->method('checkLevel')->willReturn($this->level1);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(5);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals($this->level1, $this->user_category->getLevel());
        $this->assertEquals(5, $this->user_category->getStatistic());
    }

    /**
     * Success calcul point of one user
     * without user_category already created
     */
    public function testComputeAllStatistic5()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn(null);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(5);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals(1, $this->user->getCategoryStatistics()->count());
        $this->assertEquals(1, $this->category->getUserStatistics()->count());
    }

    /**
     * Success calcul point == 0
     */
    public function testComputeAllStatistic6()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user->addCategoryStatistic($this->user_category);
        $this->category->addLevel($this->level1);
        $this->category->addLevel($this->level2);
        $this->user_category->setStatistic(3);
        $this->user->addCategoryStatistic($this->user_category);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->rankingService->expects($this->any())->method('getUserCategory')->willReturn($this->user_category);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->formulaParser->expects($this->any())->method('computeStatistic')->willReturn(0);

        $this->rankingService->computeAllStatistic();
        $this->assertEquals(null, $this->user_category->getLevel());
        $this->assertEquals(3, $this->user_category->getStatistic());
    }

    /**
     * test Users empty
     */
    public function testComputeAllStatistic7()
    {
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test users == null
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testComputeAllStatistic8()
    {
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn(null);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
        $this->assertEquals(0, $this->category->getUserStatistics()->count());
    }

    /**
     * test Categories Empty
     */
    public function testComputeAllStatistic9()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user_category->setStatistic(3);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
        $this->assertEquals(0, $this->user->getCategoryStatistics()->count());
    }

    /**
     * @expectedException Exception
     * test Categories null
     */
    public function testComputeAllStatistic10()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->user_category->setStatistic(3);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn(null);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * test satistics empty
     */
    public function testComputeAllStatistic11()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);

        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([]);
        $this->rankingService->computeAllStatistic();
        $this->assertEquals(0, $this->user->getCategoryStatistics()->count());
    }

    /**
     * @expectedException Exception
     * test satistics null
     */
    public function testComputeAllStatistic12()
    {
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn([$this->user]);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn(null);
        $this->rankingService->computeAllStatistic();
    }

    /**
     * @expectedException Exception
     * test if there is more than the maximum number of errors during the agorithm
     */
    public function testComputeAllStatistic13()
    {
        $users = [];
        for ($i = 0; $i <= RankingService::MAX_ERROR + 1; $i++) {
            array_push($users, null);
        }
        $this->categoryRepository->expects($this->any())->method('findLevelsByCategories')->willReturn([$this->category]);
        $this->userRepository->expects($this->any())->method('findUsersNotDeleted')->willReturn($users);
        $this->arrayHelper->expects($this->any())->method('mergeMapOfArray')->willReturn([1 => []]);
        $this->rankingService->computeAllStatistic();
    }


    /**
     * test all success with dataprovider
     * @dataProvider checkLevelProvider
     */
    public function testCheckLevel($step1, $step2, $stat, $step_result)
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->user_category->setStatistic($stat);
        $this->level1->setStep($step1);
        $this->level2->setStep($step2);
        $this->assertEquals($step_result, $this->rankingService->callCheckLevel($this->user_category, [$this->level1, $this->level2])->getLevel()->getStep());
    }

    /**
     * test if no level matches
     */
    public function testCheckLeve2()
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->user_category->setStatistic(2);
        $this->level1->setStep(5);
        $this->level2->setStep(10);
        $this->assertEquals(null, $this->rankingService->callCheckLevel($this->user_category, [$this->level1, $this->level2])->getLevel());
    }

    /**
     * @expectedException Error
     * test with user_category null
     */
    public function testCheckLeve3()
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->level1->setStep(5);
        $this->level2->setStep(10);
        $this->assertEquals(null, $this->rankingService->callCheckLevel(null, [$this->level1, $this->level2])->getLevel());
    }

    /**
     * @expectedException Error
     * test with 1 level null
     */
    public function testCheckLeve4()
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->user_category->setStatistic(2);
        $this->level2->setStep(10);
        $this->assertEquals(null, $this->rankingService->callCheckLevel($this->user_category, [null, $this->level2])->getLevel());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * test with 1 leves null
     */
    public function testCheckLeve5()
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->user_category->setStatistic(2);
        $this->assertEquals(null, $this->rankingService->callCheckLevel($this->user_category, null)->getLevel());
    }

    /**
     * test with 1 leves empty
     */
    public function testCheckLeve6()
    {
        $this->rankingService = $this->getMockBuilder(RankingService::class)
            ->setConstructorArgs(array($this->formulaParser, $this->manager, $this->logger,$this->arrayHelper))
            ->setMethods(null)
            ->getMock();
        $this->user_category->setStatistic(2);
        $this->assertEquals(null, $this->rankingService->callCheckLevel($this->user_category, [])->getLevel());
    }



    public function checkLevelProvider()
    {
        return [
            [5, 10, 7, 5],
            [5, 10, 11, 10],
            [5, 6, 6, 6]
        ]; //[step1,step2,stat,step_result]
    }

    private function setIdWithReflectionClass($class, $object, $id)
    {
        $reflectionClass = new ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $id);
        $reflectionProperty->setAccessible(false);
    }

}
