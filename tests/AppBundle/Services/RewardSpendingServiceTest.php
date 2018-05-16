<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/05/2018
 * Time: 16:54
 */

use AppBundle\Entity\RewardTicketConsumption;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use AppBundle\Services\RewardSpendingService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RewardSpendingServiceTest extends TestCase
{
    private $rewardSpendingService;
    private $manager;
    private $logger;

    private $contractFan;
    private $user_reward1;
    private $user_reward2;
    private $invitation_reward;
    private $consomable_reward;
    private $reduction_reward;
    private $user;
    private $contract_artist;
    private $purchase1;
    private $purchase2;
    private $counter_part1;
    private $counter_part2;
    private $step;
    private $artist;

    protected function setUp()
    {
        //entity
        $this->invitation_reward = new \AppBundle\Entity\InvitationReward();
        $this->consomable_reward = new \AppBundle\Entity\ConsomableReward();
        $this->reduction_reward = new \AppBundle\Entity\ReductionReward();
        $this->invitation_reward->setValidityPeriod(5);
        $this->consomable_reward->setValidityPeriod(5);
        $this->reduction_reward->setValidityPeriod(5);
        $this->user = new User();
        $this->purchase1 = new \AppBundle\Entity\Purchase();
        $this->purchase2 = new \AppBundle\Entity\Purchase();
        $this->counter_part1 = new \AppBundle\Entity\CounterPart();
        $this->counter_part2 = new \AppBundle\Entity\CounterPart();
        $this->setIdWithReflectionClass(\AppBundle\Entity\CounterPart::class, $this->counter_part1, 1);
        $this->setIdWithReflectionClass(\AppBundle\Entity\CounterPart::class, $this->counter_part2, 2);
        $this->contract_artist = new \AppBundle\Entity\ContractArtist();
        $this->step = new \AppBundle\Entity\Step();
        $this->artist = new \AppBundle\Entity\Artist(new \AppBundle\Entity\Phase());
        $this->contract_artist->setStep($this->step);
        $this->contractFan = new \AppBundle\Entity\ContractFan($this->contract_artist);
        $this->user_reward1 = new \AppBundle\Entity\User_Reward($this->invitation_reward, $this->user);
        $this->setIdWithReflectionClass(\AppBundle\Entity\User_Reward::class, $this->user_reward1, 1);
        $this->user_reward2 = new \AppBundle\Entity\User_Reward($this->consomable_reward, $this->user);
        $this->setIdWithReflectionClass(\AppBundle\Entity\User_Reward::class, $this->user_reward2, 2);


        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $repositories = array();
        $this->manager->expects($this->any())->method('getRepository')->will($this->returnValueMap($repositories));

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        //test
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(array('clearPurchases', 'getOrderedApplicablePurhcases', 'computeReducedPrice', 'setTicketReward', 'findCorrespondingTicket'))
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->manager);
        unset($this->rewardSpendingService);
        unset($this->logger);
        unset($this->contractFan);
        unset($this->user_reward1);
        unset($this->user_reward2);
        unset($this->invitation_reward);
        unset($this->consomable_reward);
        unset($this->reduction_reward);
        unset($this->user);
        unset($this->purchase1);
        unset($this->purchase2);
        unset($this->counter_part1);
        unset($this->counter_part2);
        unset($this->contract_artist);
        unset($this->step);
        unset($this->artist);
    }

    /**
     * success : 2 user reward of type invitation and commation
     */
    public function testApplyReward1()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->user_reward2->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(2, $this->contractFan->getUserRewards()->count());
    }

    /**
     * success : 2 user reward of type invitation and reduction
     */
    public function testApplyReward2()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->user_reward2->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(2, $this->contractFan->getUserRewards()->count());
    }

    /**
     * success : 2 user reward of type reduction ( one reduction is deleted )
     */
    public function testApplyReward3()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->user_reward2->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(1, $this->contractFan->getUserRewards()->count());
        $this->assertEquals(1, $this->contractFan->getUserRewards()->first()->getId());
    }

    /**
     * success : 2 user reward -> 1 valid and 1 non active
     */
    public function testApplyReward4()
    {
        $this->user_reward1->setActive(false)->setRemainUse(5);
        $this->user_reward2->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(1, $this->contractFan->getUserRewards()->count());
        $this->assertEquals(2, $this->contractFan->getUserRewards()->first()->getId());
    }

    /**
     * success : 2 user reward -> 1 valid and 1 with bad limit date
     */
    public function testApplyReward5()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P5D'));
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->user_reward2->setActive(true)->setRemainUse(5)->setLimitDate($date);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(1, $this->contractFan->getUserRewards()->count());
        $this->assertEquals(1, $this->contractFan->getUserRewards()->first()->getId());
    }

    /**
     * success : 2 user reward -> 1 valid and 1 with bad remain use
     */
    public function testApplyReward6()
    {
        $this->user_reward1->setActive(true)->setRemainUse(0);
        $this->user_reward2->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->contractFan->addUserReward($this->user_reward2);
        $this->rewardSpendingService->expects($this->any())->method('getOrderedApplicablePurhcases')->willReturn([$this->purchase1]);
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(1, $this->contractFan->getUserRewards()->count());
        $this->assertEquals(2, $this->contractFan->getUserRewards()->first()->getId());
    }

    /**
     * success : contract fan without reward
     */
    public function testApplyReward7()
    {
        $this->rewardSpendingService->applyReward($this->contractFan);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * @expectedException TypeError
     * error : contract fan is null
     */
    public function testApplyReward8()
    {
        $this->rewardSpendingService->applyReward(null);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * success : consume on non reduction reward. does not consume all uses. with 1 purchases
     */
    public function testConsumeReward1()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setQuantity(3);
        $this->contractFan->addPurchase($this->purchase1);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(2, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume on non reduction reward. does not consume all uses .with 2 purchases
     */
    public function testConsumeReward2()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setQuantity(3);
        $this->purchase2->setQuantity(1);
        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(1, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume on non reduction reward.  consume all uses .with 1 purchases
     */
    public function testConsumeReward3()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setQuantity(7);
        $this->contractFan->addPurchase($this->purchase1);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(0, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume on non reduction reward. consume all uses .with 2 purchases
     */
    public function testConsumeReward4()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setQuantity(4);
        $this->purchase2->setQuantity(2);
        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(0, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume reduction reward. does not consume all uses. with 1 purchases
     */
    public function testConsumeReward5()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setNbReducedCounterparts(3);
        $this->contractFan->addPurchase($this->purchase1);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(2, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume reduction reward. consume all uses. with 1 purchases
     */
    public function testConsumeReward6()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setNbReducedCounterparts(2);
        $this->purchase2->setNbReducedCounterparts(2);
        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(1, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume reduction reward. does not consume all uses. with 2 purchases
     */
    public function testConsumeReward7()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setNbReducedCounterparts(2);
        $this->purchase2->setNbReducedCounterparts(2);
        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(1, $this->user_reward1->getRemainUse());
    }

    /**
     * success : consume reduction reward. consume all uses. with 2 purchases
     */
    public function testConsumeReward8()
    {
        $this->user_reward1->setActive(true)->setRemainUse(5)->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->purchase1->setNbReducedCounterparts(3);
        $this->purchase2->setNbReducedCounterparts(4);
        $this->contractFan->addPurchase($this->purchase1);
        $this->contractFan->addPurchase($this->purchase2);
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(0, $this->user_reward1->getRemainUse());
    }

    /**
     * success : cf doesn't have reward
     */
    public function testConsumeReward9()
    {
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * success : cf doesn't have reward
     */
    public function testConsumeReward10()
    {
        $this->rewardSpendingService->consumeReward($this->contractFan);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * @expectedException TypeError
     * success : cf is null
     */
    public function testConsumeReward11()
    {
        $this->rewardSpendingService->consumeReward(null);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * success  :  no restrictions 2 reward
     */
    public function testGetApplicableReward1()
    {
        $this->contractFan->addUserReward($this->user_reward1)->addUserReward($this->user_reward2);
        $this->assertEquals([$this->user_reward1, $this->user_reward2], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on contract artist(multiple linked to reward) 1 reward -- OK
     */
    public function testGetApplicableReward2()
    {
        $this->user_reward1->addBaseContractArtist($this->contract_artist, new \AppBundle\Entity\ContractArtist());
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on contract artist 1 reward -- KO
     */
    public function testGetApplicableReward3()
    {
        $this->user_reward1->addBaseContractArtist(new \AppBundle\Entity\ContractArtist());
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on step(multiple linked to reward) 1 reward -- OK
     */
    public function testGetApplicableReward4()
    {
        $this->user_reward1->addBaseStep($this->step, new \AppBundle\Entity\Step());
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on step 1 reward -- KO
     */
    public function testGetApplicableReward5()
    {
        $this->user_reward1->addBaseStep(new \AppBundle\Entity\Step());
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on artist 1 reward(multiple linked to reward) -- OK
     */
    public function testGetApplicableReward6()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addArtist($this->artist, new \AppBundle\Entity\Artist(new \AppBundle\Entity\Phase()));
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on artist 1 reward -- KO
     */
    public function testGetApplicableReward7()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addArtist(new \AppBundle\Entity\Artist(new \AppBundle\Entity\Phase()));
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on counterpart 1 reward(multiple linked to reward) -- OK
     */
    public function testGetApplicableReward8()
    {
        $this->user_reward1->addCounterPart($this->counter_part1, new \AppBundle\Entity\CounterPart());
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  restrictions on counterpart 1 reward-- KO
     */
    public function testGetApplicableReward9()
    {
        $this->user_reward1->addCounterPart(new \AppBundle\Entity\CounterPart());
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with 2 restrictions ( contract artist && artist ) -- OK
     */
    public function testGetApplicableReward10()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist($this->contract_artist)->addArtist($this->artist);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with 2 restrictions ( contract artist && artist ) -- KO
     */
    public function testGetApplicableReward11()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist(new \AppBundle\Entity\ContractArtist())->addArtist($this->artist);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with 3 restrictions ( contract artist && artist && step ) -- OK
     */
    public function testGetApplicableReward12()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist($this->contract_artist)->addArtist($this->artist)->addBaseStep($this->step);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with 3 restrictions ( contract artist && artist && step ) -- OK
     */
    public function testGetApplicableReward13()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist($this->contract_artist)->addArtist($this->artist)->addBaseStep(new \AppBundle\Entity\Step());
        $this->contractFan->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with all restrictions -- OK
     */
    public function testGetApplicableReward14()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist($this->contract_artist)
            ->addArtist($this->artist)
            ->addBaseStep($this->step)
            ->addCounterPart($this->counter_part1);
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->assertEquals([$this->user_reward1], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  with all restrictions -- KO
     */
    public function testGetApplicableReward15()
    {
        $this->contract_artist->setArtist($this->artist);
        $this->user_reward1->addBaseContractArtist($this->contract_artist)
            ->addArtist($this->artist)
            ->addBaseStep($this->step)
            ->addCounterPart($this->counter_part2);
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addPurchase($this->purchase1)->addUserReward($this->user_reward1);
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * success  :  cf doesn't have user_reward
     */
    public function testGetApplicableReward16()
    {
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward($this->contractFan));
    }

    /**
     * @expectedException TypeError
     * error  :  cf is null
     */
    public function testGetApplicableReward17()
    {
        $this->assertEquals([], $this->rewardSpendingService->getApplicableReward(null));
    }

    /**
     * Success : calcul reduced price with enough remain use and quantity
     */
    public function testComputeReducedPrice1()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 10]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(5);//quantity organic
        $remain_use = 10;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(10.8, $this->purchase1->getReducedPrice());
        $this->assertEquals(5, $result);
        $this->assertEquals(54, $this->contractFan->getAmount());
    }

    /**
     * Success : calcul reduced price with enough remain use and but not enough quantity
     */
    public function testComputeReducedPrice2()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 10]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(3);//quantity organic
        $remain_use = 10;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(10.8, $this->purchase1->getReducedPrice());
        $this->assertEquals(7, $result);
        $this->assertEquals(32.4, $this->contractFan->getAmount());
    }

    /**
     * Success : calcul reduced price with enough quantity and but not enough remain use
     */
    public function testComputeReducedPrice3()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 10]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(5);//quantity organic
        $remain_use = 3;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(10.8, $this->purchase1->getReducedPrice());
        $this->assertEquals(0, $result);
        $this->assertEquals(56.4, $this->contractFan->getAmount());
    }

    /**
     * Success : calcul reduced price with to hight reduction
     */
    public function testComputeReducedPrice4()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 110]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(5);//quantity organic
        $remain_use = 3;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(0, $this->purchase1->getReducedPrice());
        $this->assertEquals(0, $result);
        $this->assertEquals(24, $this->contractFan->getAmount());
    }

    /**
     * Success : calcul remain_use = 0
     */
    public function testComputeReducedPrice5()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 70]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(5);//quantity organic
        $remain_use = 0;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(null, $this->purchase1->getReducedPrice());
        $this->assertEquals(0, $result);
        $this->assertEquals(60, $this->contractFan->getAmount());
    }

    /**
     * Success : calcul quantity == 0
     */
    public function testComputeReducedPrice6()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->setReward($this->reduction_reward)->setRewardTypeParameters(['reduction' => 70]);
        $this->counter_part1->setPrice(12);
        $this->purchase1->setQuantity(0);//quantity organic
        $remain_use = 3;
        $this->purchase1->setCounterPart($this->counter_part1);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1);
        $this->contractFan->setAmount($this->counter_part1->getPrice() * $this->purchase1->getQuantity());
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, $remain_use);
        $this->assertEquals(null, $this->purchase1->getReducedPrice());
        $this->assertEquals(3, $result);
        $this->assertEquals(0, $this->contractFan->getAmount());
    }

    /**
     * @expectedException TypeError
     * error : cf null
     */
    public function testComputeReducedPrice7()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $result = $this->rewardSpendingService->callComputeReducedPrice(null, $this->user_reward1, $this->purchase1, 5);
        $this->assertEquals(null, $this->contractFan->getAmount());
    }

    /**
     * @expectedException TypeError
     * error : user_reward null
     */
    public function testComputeReducedPrice8()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, null, $this->purchase1, 5);
        $this->assertEquals(null, $this->contractFan->getAmount());
    }

    /**
     * @expectedException TypeError
     * error : purchase null
     */
    public function testComputeReducedPrice9()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $result = $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, null, 5);
        $this->assertEquals(null, $this->contractFan->getAmount());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     * error : user_reward don't have reduction
     */
    public function testComputeReducedPrice10()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->rewardSpendingService->callComputeReducedPrice($this->contractFan, $this->user_reward1, $this->purchase1, 5);
        $this->assertEquals(null, $this->contractFan->getAmount());
    }

    /**
     * success : clear all purchase of contract fan
     */
    public function testClearPurchases1()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setReducedPrice(5)->setNbReducedCounterparts(2);
        $this->purchase2->setReducedPrice(7)->setNbReducedCounterparts(3);
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->callClearPurchases($this->contractFan);
        $this->assertTrue($this->purchase1->getReducedPrice() == null && $this->purchase1->getNbReducedCounterparts() == 0);
        $this->assertTrue($this->purchase2->getReducedPrice() == null && $this->purchase2->getNbReducedCounterparts() == 0);
    }

    /**
     * cf without purchase
     */
    public function testClearPurchases2()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->rewardSpendingService->callClearPurchases($this->contractFan);
        $this->assertTrue($this->purchase1->getReducedPrice() == null && $this->purchase1->getNbReducedCounterparts() == 0);
        $this->assertTrue($this->purchase2->getReducedPrice() == null && $this->purchase2->getNbReducedCounterparts() == 0);
    }

    /**
     * @expectedException TypeError
     * cf null
     */
    public function testClearPurchases3()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->rewardSpendingService->callClearPurchases(null);
        $this->assertTrue($this->purchase1->getReducedPrice() == null && $this->purchase1->getNbReducedCounterparts() == 0);
        $this->assertTrue($this->purchase2->getReducedPrice() == null && $this->purchase2->getNbReducedCounterparts() == 0);
    }

    /**
     * success :  cf with 2 purchases applicable
     */
    public function testGetOrderedApplicablePurhcases1()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->addCounterPart($this->counter_part1)->addCounterPart($this->counter_part2);
        $this->purchase1->setCounterPart($this->counter_part1->setPrice(3));
        $this->purchase2->setCounterPart($this->counter_part2->setPrice(4));
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->assertEquals([$this->purchase2, $this->purchase1], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * success :  cf with 1 purchases applicable
     */
    public function testGetOrderedApplicablePurhcases2()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->user_reward1->addCounterPart($this->counter_part2);
        $this->purchase1->setCounterPart($this->counter_part1->setPrice(3));
        $this->purchase2->setCounterPart($this->counter_part2->setPrice(4));
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->assertEquals([$this->purchase2], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * success :  cf with 0 restriction counter part
     */
    public function testGetOrderedApplicablePurhcases3()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setCounterPart($this->counter_part1->setPrice(3));
        $this->purchase2->setCounterPart($this->counter_part2->setPrice(4));
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->assertEquals([$this->purchase2, $this->purchase1], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * success :  cf with 0 restriction counter part (inversed order)
     */
    public function testGetOrderedApplicablePurhcases4()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setCounterPart($this->counter_part1->setPrice(4));
        $this->purchase2->setCounterPart($this->counter_part2->setPrice(3));
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->assertEquals([$this->purchase1, $this->purchase2], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * success :  cf with 0 purchase
     */
    public function testGetOrderedApplicablePurhcases5()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setCounterPart($this->counter_part1->setPrice(4));
        $this->purchase2->setCounterPart($this->counter_part2->setPrice(3));
        $this->assertEquals([], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * @expectedException TypeError
     * success :  cf null
     */
    public function testGetOrderedApplicablePurhcases6()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->assertEquals([], $this->rewardSpendingService->getOrderedApplicablePurhcases(null, $this->user_reward1));
    }

    /**
     * @expectedException TypeError
     * success :  user_reward null
     */
    public function testGetOrderedApplicablePurhcases7()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->assertEquals([], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, null));
    }

    /**
     * @expectedException Error
     * success :  counter part with null price
     */
    public function testGetOrderedApplicablePurhcases8()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->contractFan->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->assertEquals([], $this->rewardSpendingService->getOrderedApplicablePurhcases($this->contractFan, $this->user_reward1));
    }

    /**
     * success : set ticket reward non reduction
     * enough remain use for quantity
     */
    public function testSetTicketReward1()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setQuantity(2);
        $this->purchase2->setQuantity(5);
        $this->user_reward1->setRemainUse(10);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [$this->purchase1, $this->purchase2], $this->contractFan);
        $this->assertEquals(7, $this->contractFan->getTicketRewards()->count());
        $this->assertEquals(2, $this->purchase1->getTicketRewards()->count());
        $this->assertEquals(5, $this->purchase2->getTicketRewards()->count());
    }

    /**
     * success : set ticket reward non reduction
     * not enough remain use for quantity
     */
    public function testSetTicketReward2()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setQuantity(2);
        $this->purchase2->setQuantity(5);
        $this->user_reward1->setRemainUse(4);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [$this->purchase1, $this->purchase2], $this->contractFan);
        $this->assertEquals(4, $this->contractFan->getTicketRewards()->count());
        $this->assertEquals(2, $this->purchase1->getTicketRewards()->count());
        $this->assertEquals(2, $this->purchase2->getTicketRewards()->count());
    }

    /**
     * success : set ticket reward reduction
     */
    public function testSetTicketReward3()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setNbReducedCounterparts(2);
        $this->purchase2->setNbReducedCounterparts(5);
        $this->user_reward1->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [$this->purchase1, $this->purchase2], $this->contractFan);
        $this->assertEquals(7, $this->contractFan->getTicketRewards()->count());
        $this->assertEquals(2, $this->purchase1->getTicketRewards()->count());
        $this->assertEquals(5, $this->purchase2->getTicketRewards()->count());
    }

    /**
     * @expectedException TypeError
     * error : user_reward null
     *
     */
    public function testSetTicketReward4()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->purchase1->setNbReducedCounterparts(2);
        $this->purchase2->setNbReducedCounterparts(5);
        $this->user_reward1->setReward($this->reduction_reward);
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward(null, [$this->purchase1, $this->purchase2], $this->contractFan);
        $this->assertEquals(7, $this->contractFan->getTicketRewards()->count());
        $this->assertEquals(2, $this->purchase1->getTicketRewards()->count());
        $this->assertEquals(5, $this->purchase2->getTicketRewards()->count());
    }

    /**
     * success : purchases empty
     *
     */
    public function testSetTicketReward5()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [], $this->contractFan);
        $this->assertEquals(0, $this->contractFan->getTicketRewards()->count());
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * success : purchases null
     *
     */
    public function testSetTicketReward6()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, null, $this->contractFan);
        $this->assertEquals(0, $this->contractFan->getTicketRewards()->count());
    }

    /**
     * @expectedException TypeError
     * success : cf null
     *
     */
    public function testSetTicketReward7()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [$this->purchase1], null);
        $this->assertEquals(0, $this->contractFan->getTicketRewards()->count());
    }

    /**
     * @expectedException TypeError
     * success : purchases array null
     *
     */
    public function testSetTicketReward8()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $this->contractFan->addUserReward($this->user_reward1)->addPurchase($this->purchase1)->addPurchase($this->purchase2);
        $this->rewardSpendingService->setTicketReward($this->user_reward1, [null], null);
        $this->assertEquals(0, $this->contractFan->getTicketRewards()->count());
    }

    /**
     * assigns a reward to a ticket
     */
    public function testGiveRewardToTicket1()
    {
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user)->addContract($this->contractFan);
        $ticket_reward1 = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $this->contractFan->addTicket($ticket1);
        $this->contractFan->addTicketReward($ticket_reward1);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->rewardSpendingService->expects($this->any())->method('findCorrespondingTicket')->willReturn([[], $ticket1]);
        $this->rewardSpendingService->giveRewardToTicket($this->contractFan);
        $this->assertEquals(1, $ticket1->getRewards()->count());
    }

    /**
     * assigns 0 reward to a ticket
     */
    public function testGiveRewardToTicket2()
    {
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user)->addContract($this->contractFan);
        $ticket_reward1 = new RewardTicketConsumption($this->user_reward2, null, false, true);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $this->contractFan->addTicket($ticket1);
        $this->contractFan->addTicketReward($ticket_reward1);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->rewardSpendingService->expects($this->any())->method('findCorrespondingTicket')->willReturn([[], $ticket1]);
        $this->rewardSpendingService->giveRewardToTicket($this->contractFan);
        $this->assertEquals(0, $ticket1->getRewards()->count());
    }

    /**
     * cf doesn't have reward
     */
    public function testGiveRewardToTicket3()
    {
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user)->addContract($this->contractFan);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $this->rewardSpendingService->expects($this->any())->method('findCorrespondingTicket')->willReturn([[], $ticket1]);
        $this->rewardSpendingService->giveRewardToTicket($this->contractFan);
        $this->assertEquals(0, $ticket1->getRewards()->count());
    }

    /**
     * @expectedException TypeError
     * cf doesn't have reward
     */
    public function testGiveRewardToTicket4()
    {
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user)->addContract($this->contractFan);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $this->rewardSpendingService->expects($this->any())->method('findCorrespondingTicket')->willReturn([[], $ticket1]);
        $this->rewardSpendingService->giveRewardToTicket(null);
        $this->assertEquals(0, $ticket1->getRewards()->count());
    }

    /**
     * cf doesn't have reward ticket
     */
    public function testGiveRewardToTicket5()
    {
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user)->addContract($this->contractFan);
        $this->contractFan->addUserReward($this->user_reward1);
        $this->rewardSpendingService->giveRewardToTicket($this->contractFan);
        $this->assertEquals(0, $this->contractFan->getTicketRewards()->count());
    }

    /**
     * success : Find the corresponding ticket among 2 tickets
     */
    public function testFindCorrespondingTicket1()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user);
        $cart->addContract($this->contractFan);
        $this->purchase1->setCounterPart($this->counter_part1);
        $ticket_reward = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket_reward->setPurchase($this->purchase1);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $ticket2 = new Ticket($this->contractFan, $this->counter_part2, 2, 20);
        $this->assertEquals([[$ticket2], $ticket1], $this->rewardSpendingService->callFindCorrespondingTicket([$ticket1, $ticket2], $ticket_reward));
    }

    /**
     * success : doesn't find the corresponding ticket among 2 tickets
     */
    public function testFindCorrespondingTicket2()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user);
        $cart->addContract($this->contractFan);
        $this->purchase1->setCounterPart($this->counter_part2);
        $ticket_reward = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket_reward->setPurchase($this->purchase1);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $ticket2 = new Ticket($this->contractFan, $this->counter_part1, 2, 20);
        $this->assertEquals([[$ticket1,$ticket2], null], $this->rewardSpendingService->callFindCorrespondingTicket([$ticket1, $ticket2], $ticket_reward));
    }

    /**
     * @expectedException Error
     * error : ticket reward = null
     */
    public function testFindCorrespondingTicket3()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $cart = new \AppBundle\Entity\Cart();
        $cart->setUser($this->user);
        $cart->addContract($this->contractFan);
        $this->purchase1->setCounterPart($this->counter_part2);
        $ticket_reward = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket_reward->setPurchase($this->purchase1);
        $ticket1 = new Ticket($this->contractFan, $this->counter_part1, 1, 12);
        $ticket2 = new Ticket($this->contractFan, $this->counter_part1, 2, 20);
        $this->assertEquals([[$ticket1,$ticket2], null], $this->rewardSpendingService->callFindCorrespondingTicket([$ticket1, $ticket2], null));
    }

    /**
     * success : success empty
     */
    public function testFindCorrespondingTicket4()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $ticket_reward = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $this->assertEquals([[], null], $this->rewardSpendingService->callFindCorrespondingTicket([], $ticket_reward));
    }
    /**
     * @expectedException Error
     * error : array ticket with null ticket
     */
    public function testFindCorrespondingTicket5()
    {
        $this->rewardSpendingService = $this->getMockBuilder(RewardSpendingService::class)
            ->setConstructorArgs(array($this->manager, $this->logger))
            ->setMethods(null)
            ->getMock();
        $ticket_reward = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $this->assertEquals([[], null], $this->rewardSpendingService->callFindCorrespondingTicket([null,null], $ticket_reward));
    }


    /**
     * success : refund all reward refundable and set remain use and active
     */
    public function testRefundReward1()
    {
        $this->user_reward1->setRemainUse(2)->setActive(true);
        $this->user_reward2->setRemainUse(0)->setActive(false);
        $ticket_reward1 = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket_reward2 = new RewardTicketConsumption($this->user_reward2, null, false, false);
        $this->contractFan->addTicketReward($ticket_reward1)->addTicketReward($ticket_reward2);
        $this->rewardSpendingService->refundReward($this->contractFan);
        $this->assertEquals(3, $this->user_reward1->getRemainUse());
        $this->assertTrue($this->user_reward1->getActive());
        $this->assertEquals(0, $this->user_reward2->getRemainUse());
        $this->assertFalse($this->user_reward2->getActive());
    }

    /**
     * success : refund all reward refundable and set remain use and active
     */
    public function testRefundReward2()
    {
        $this->user_reward1->setRemainUse(0)->setActive(false);
        $this->user_reward2->setRemainUse(0)->setActive(false);
        $ticket_reward1 = new RewardTicketConsumption($this->user_reward1, null, false, true);
        $ticket_reward2 = new RewardTicketConsumption($this->user_reward1, null, false, false);
        $this->contractFan->addTicketReward($ticket_reward1)->addTicketReward($ticket_reward2);
        $this->rewardSpendingService->refundReward($this->contractFan);
        $this->assertEquals(1, $this->user_reward1->getRemainUse());
        $this->assertTrue($this->user_reward1->getActive());
        $this->assertEquals(0, $this->user_reward2->getRemainUse());
        $this->assertFalse($this->user_reward2->getActive());
    }

    /**
     * success : cf doesn't have user reward
     */
    public function testRefundReward3()
    {
        $this->rewardSpendingService->refundReward($this->contractFan);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
    }

    /**
     * @expectedException TypeError
     * success : cf null
     */
    public function testRefundReward4()
    {
        $this->rewardSpendingService->refundReward(null);
        $this->assertEquals(0, $this->contractFan->getUserRewards()->count());
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
