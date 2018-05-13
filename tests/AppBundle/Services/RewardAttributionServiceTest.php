<?php
/**
 * Created by PhpStorm.
 * User: jcochart
 * Date: 13/05/2018
 * Time: 20:42
 */

use AppBundle\Entity\Reward;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\RewardAttributionService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RewardAttributionServiceTest extends TestCase
{
    //MOCK
    private $rewardAttributionService;
    private $notificationDispatcher;
    private $mailDispatcher;
    private $manager;
    private $logger;

    private $rewardRestrictionRepository;
    private $contractArtistRepository;
    private $artistRepository;
    private $counterPartRepository;
    private $stepRepository;
    private $rewardRepository;


    private $user_category1;
    private $user_category2;
    private $invitation_reward;
    private $consomable_reward;
    private $user;
    private $restriction;
    private $contractArtist;
    private $artist;
    private $counterpart;
    private $step;
    private $user_reward;


    protected function setUp()
    {
        //repository
        $this->rewardRestrictionRepository = $this->getMockBuilder(\AppBundle\Repository\RewardRestrictionRepository::class)->disableOriginalConstructor()->getMock();
        $this->contractArtistRepository = $this->getMockBuilder(\AppBundle\Repository\ContractArtistRepository::class)->disableOriginalConstructor()->getMock();
        $this->artistRepository = $this->getMockBuilder(\AppBundle\Repository\ArtistRepository::class)->disableOriginalConstructor()->getMock();
        $this->counterPartRepository = $this->getMockBuilder(\AppBundle\Repository\CounterPartRepository::class)->disableOriginalConstructor()->getMock();
        $this->stepRepository = $this->getMockBuilder(\AppBundle\Repository\StepRepository::class)->disableOriginalConstructor()->getMock();
        $this->rewardRepository = $this->getMockBuilder(\AppBundle\Repository\RewardRepository::class)->disableOriginalConstructor()->getMock();

        //entity
        $this->user = new \AppBundle\Entity\User();
        $this->invitation_reward = new \AppBundle\Entity\InvitationReward();
        $this->invitation_reward->setValidityPeriod(3);
        $this->consomable_reward = new \AppBundle\Entity\ConsomableReward();
        $this->consomable_reward->setValidityPeriod(3);
        $this->user_category1 = new \AppBundle\Entity\User_Category();
        $this->user_category2 = new \AppBundle\Entity\User_Category();
        $this->restriction = new \AppBundle\Entity\RewardRestriction();
        $this->contractArtist = new \AppBundle\Entity\ContractArtist();
        $this->artist = new \AppBundle\Entity\Artist(new \AppBundle\Entity\Phase());
        $this->counterpart = new \AppBundle\Entity\CounterPart();
        $this->step = new \AppBundle\Entity\Step();
        $this->user_reward = new \AppBundle\Entity\User_Reward($this->consomable_reward,new \AppBundle\Entity\User());


        // service
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $repositories = array(
            array("AppBundle:RewardRestriction", $this->rewardRestrictionRepository),
            array("AppBundle:ContractArtist", $this->contractArtistRepository),
            array("AppBundle:Artist", $this->artistRepository),
            array("AppBundle:CounterPart", $this->counterPartRepository),
            array("AppBundle:Step", $this->stepRepository),
            array("AppBundle:Reward",$this->rewardRepository)
        );
        $this->manager->expects($this->any())->method('getRepository')->will($this->returnValueMap($repositories));

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->mailDispatcher = $this->getMockBuilder(MailDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->mailDispatcher->expects($this->any())->method('sendEmailRewardAttribution');
        $this->notificationDispatcher = $this->getMockBuilder(NotificationDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->notificationDispatcher->expects($this->any())->method('notifyRewardAttribution');

        //test
        $this->rewardAttributionService = $this->getMockBuilder(RewardAttributionService::class)
            ->setConstructorArgs(array($this->notificationDispatcher, $this->mailDispatcher, $this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->rewardAttributionService);
        unset($this->notificationDispatcher);
        unset($this->mailDispatcher);
        unset($this->manager);
        unset($this->logger);
        unset($this->user);
        unset($this->invitation_reward);
        unset($this->consomable_reward);
        unset($this->user_category2);
        unset($this->user_category1);
        unset($this->rewardRestrictionRepository);
        unset($this->contractArtistRepository);
        unset($this->stepRepository);
        unset($this->artistRepository);
        unset($this->counterPartRepository);
        unset($this->contractArtist);
        unset($this->artist);
        unset($this->counterpart);
        unset($this->step);
        unset($this->user_reward);
    }

    /**
     * Success : only one reward given
     */
    public function testGiveReward1()
    {
        $this->user_category1->setUser($this->user);
        $stats = [$this->user_category1];
        $this->rewardAttributionService->giveReward($stats, $this->invitation_reward, 'notif', 'email', 'emailcontent');
        $this->assertEquals(1, $this->user->getRewards()->count());
    }

    /**
     * Success : two reward given
     */
    public function testGiveReward2()
    {
        $this->user_category1->setUser($this->user);
        $this->user_category2->setUser($this->user);
        $stats = [$this->user_category1, $this->user_category2];
        $this->rewardAttributionService->giveReward($stats, $this->invitation_reward, 'notif', 'email', 'emailcontent');
        $this->assertEquals(2, $this->user->getRewards()->count());
    }

    /**
     * Success : no stat
     */
    public function testGiveReward3()
    {
        $this->assertNull($this->rewardAttributionService->giveReward([], $this->invitation_reward, 'notif', 'email', 'emailcontent'));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : stats null
     */
    public function testGiveReward4()
    {
        $this->rewardAttributionService->giveReward(null, $this->invitation_reward, 'notif', 'email', 'emailcontent');
    }

    /**
     * @expectedException TypeError
     * error : reward null
     */
    public function testGiveReward5()
    {
        $this->user_category1->setUser($this->user);
        $stats = [$this->user_category1];
        $this->rewardAttributionService->giveReward($stats, null, 'notif', 'email', 'emailcontent');
    }

    /**
     * @expectedException Exception
     * error : reward with null validity date
     */
    public function testGiveReward6()
    {
        $this->user_category1->setUser($this->user);
        $stats = [$this->user_category1];
        $this->rewardAttributionService->giveReward($stats, new \AppBundle\Entity\InvitationReward(), 'notif', 'email', 'emailcontent');
    }

    /**
     * @expectedException TypeError
     * error : user_category without user
     */
    public function testGiveReward7()
    {
        $stats = [$this->user_category1];
        $this->rewardAttributionService->giveReward($stats, new \AppBundle\Entity\InvitationReward(), 'notif', 'email', 'emailcontent');
    }

    /**
     * success most confirmed concert
     */
    public function testDefineRestriction1()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::MOST_CONFIRMED_CONCERT);
        $this->rewardRestrictionRepository->expects($this->any())->method('getMostRecentConfirmedConcert')->willReturn($this->contractArtist);
        $this->assertEquals(0,$this->user_reward->getBaseContractArtists()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getBaseContractArtists()->count());
        $this->assertEquals($this->contractArtist,$this->user_reward->getBaseContractArtists()->toArray()[0]);
    }

    /**
     * success one selected concert
     */
    public function testDefineRestriction2()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_CONCERT_SELECTED);
        $this->contractArtistRepository->expects($this->any())->method('find')->willReturn($this->contractArtist);
        $this->assertEquals(0,$this->user_reward->getBaseContractArtists()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getBaseContractArtists()->count());
        $this->assertEquals($this->contractArtist,$this->user_reward->getBaseContractArtists()->toArray()[0]);
    }

    /**
     * success one selected artist
     */
    public function testDefineRestriction3()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_ARTIST_SELECTED);
        $this->artistRepository->expects($this->any())->method('find')->willReturn($this->artist);
        $this->assertEquals(0,$this->user_reward->getArtists()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getArtists()->count());
        $this->assertEquals($this->artist,$this->user_reward->getArtists()->toArray()[0]);
    }

    /**
     * success one selected artist
     */
    public function testDefineRestriction4()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_COUNTERPART_SELECTED);
        $this->counterPartRepository->expects($this->any())->method('find')->willReturn($this->counterpart);
        $this->assertEquals(0,$this->user_reward->getCounterParts()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getCounterParts()->count());
        $this->assertEquals($this->counterpart,$this->user_reward->getCounterParts()->toArray()[0]);
    }

    /**
     * success one selected artist
     */
    public function testDefineRestriction5()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_STEP_SELECTED);
        $this->stepRepository->expects($this->any())->method('find')->willReturn($this->step);
        $this->assertEquals(0,$this->user_reward->getBaseSteps()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getBaseSteps()->count());
        $this->assertEquals($this->step,$this->user_reward->getBaseSteps()->toArray()[0]);
    }

    /**
     * @expectedException TypeError
     * error bad type for querry
     */
    public function testDefineRestriction6()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_CONCERT_SELECTED);
        $this->stepRepository->expects($this->any())->method('find')->willReturn($this->step);
        $this->assertEquals(0,$this->user_reward->getBaseSteps()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getBaseSteps()->count());
        $this->assertEquals($this->step,$this->user_reward->getBaseSteps()->toArray()[0]);
    }

    /**
     * @expectedException TypeError
     * error restriction is null
     */
    public function testDefineRestriction7()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_STEP_SELECTED);
        $this->stepRepository->expects($this->any())->method('find')->willReturn($this->step);
        $this->assertEquals(0,$this->user_reward->getBaseSteps()->count());
        $this->rewardAttributionService->defineRestriction(null,$this->user_reward);
        $this->assertEquals(1,$this->user_reward->getBaseSteps()->count());
        $this->assertEquals($this->step,$this->user_reward->getBaseSteps()->toArray()[0]);
    }

    /**
     * @expectedException TypeError
     * error user_reward is null
     */
    public function testDefineRestriction8()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_STEP_SELECTED);
        $this->stepRepository->expects($this->any())->method('find')->willReturn($this->step);
        $this->assertEquals(0,$this->user_reward->getBaseSteps()->count());
        $this->rewardAttributionService->defineRestriction($this->restriction,null);
        $this->assertEquals(1,$this->user_reward->getBaseSteps()->count());
        $this->assertEquals($this->step,$this->user_reward->getBaseSteps()->toArray()[0]);
    }

    /**
     * @expectedException TypeError
     * error repository return null
     */
    public function testDefineRestriction9()
    {
        $this->restriction->setQueryParameter("1|TEST");
        $this->restriction->setQuery(RewardAttributionService::ONE_STEP_SELECTED);
        $this->stepRepository->expects($this->any())->method('find')->willReturn(null);
        $this->rewardAttributionService->defineRestriction($this->restriction,$this->user_reward);
        $this->assertEquals(0,$this->user_reward->getBaseSteps()->count());
    }

    /**
     * success with 3 different reward type
     */
    public function testConstructRewardSelectWithType1(){
        $reward1 = new \AppBundle\Entity\InvitationReward();
        $reward2 = new \AppBundle\Entity\ConsomableReward();
        $reward3 = new \AppBundle\Entity\ReductionReward();
        $rewards = [$reward1,$reward2,$reward3];
        $return = ["Consommation" => [$reward2],"Invitation" => [$reward1], "Reduction" => [$reward3]];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }

    /**
     * success with 2 different reward type
     */
    public function testConstructRewardSelectWithType2(){
        $reward1 = new \AppBundle\Entity\InvitationReward();
        $reward2 = new \AppBundle\Entity\InvitationReward();
        $reward3 = new \AppBundle\Entity\InvitationReward();
        $rewards = [$reward1,$reward2,$reward3];
        $return = ["Invitation" => [$reward1,$reward2,$reward3]];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }

    /**
     * success with 3 same reward type
     */
    public function testConstructRewardSelectWithType3(){
        $reward1 = new \AppBundle\Entity\InvitationReward();
        $reward2 = new \AppBundle\Entity\ConsomableReward();
        $rewards = [$reward1,$reward2];
        $return = ["Consommation" => [$reward2],"Invitation" => [$reward1]];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }

    /**
     * success with 2 same reward type and 1 different entity
     */
    public function testConstructRewardSelectWithType4(){
        $reward1 = new \AppBundle\Entity\InvitationReward();
        $reward2 = new \AppBundle\Entity\ConsomableReward();
        $reward3 = new \AppBundle\Entity\User();
        $rewards = [$reward1,$reward2,$reward3];
        $return = ["Consommation" => [$reward2],"Invitation" => [$reward1], "Autres" => [$reward3]];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }

    /**
     * success rewards is empty
     */
    public function testConstructRewardSelectWithType5(){
        $rewards = [];
        $return = [];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error rewards == null
     */
    public function testConstructRewardSelectWithType6(){
        $rewards = null;
        $return = [];
        $this->rewardRepository->expects($this->any())->method('findNotDeletedRewards')->willReturn($rewards);
        $this->assertEquals($return,$this->rewardAttributionService->constructRewardSelectWithType("fr"));
    }
}
