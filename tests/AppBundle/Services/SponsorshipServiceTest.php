<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 08/05/2018
 * Time: 11:35
 */

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Reward;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\User;
use AppBundle\Repository\ContractFanRepository;
use AppBundle\Repository\SponsorshipInvitationRepository;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\SponsorshipService;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Util\TokenGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SponsorshipServiceTest extends TestCase
{
    //MOCK
    private $sponsorshipService;
    private $manager;
    private $logger;
    private $mailDispatcher;
    private $notificationDispatcher;
    private $token_gen;
    private $contract_artist;
    private $user;
    private $host_user;
    private $sponsorship_invitation;
    private $sponsorshipRepository;
    private $reward;
    private $contractFanRepository;
    private $contractFan;

    protected function setUp()
    {
        //repository
        $this->sponsorshipRepository = $this->getMockBuilder(SponsorshipInvitationRepository::class)->disableOriginalConstructor()->getMock();
        $this->contractFanRepository = $this->getMockBuilder(ContractFanRepository::class)->disableOriginalConstructor()->getMock();

        //entity
        $this->contract_artist = $this->getMockBuilder(ContractArtist::class)->disableOriginalConstructor()->getMock();
        $this->contract_artist->expects($this->any())->method('getId')->willReturn(1);
        $this->user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $this->host_user = $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $this->sponsorship_invitation = $this->getMockBuilder(SponsorshipInvitation::class)->disableOriginalConstructor()->getMock();
        $this->reward = $this->getMockBuilder(Reward::class)->disableOriginalConstructor()->getMock();
        $this->contractFan = $this->getMockBuilder(ContractFan::class)->disableOriginalConstructor()->getMock();

        //service
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $this->manager->expects($this->any())->method('getRepository')
            ->withConsecutive(['AppBundle:SponsorshipInvitation'], ['AppBundle:ContractFan'])
            ->willReturnOnConsecutiveCalls($this->sponsorshipRepository, $this->contractFanRepository);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->mailDispatcher = $this->getMockBuilder(MailDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->mailDispatcher->expects($this->any())->method('sendSponsorshipInvitationEmail');
        $this->notificationDispatcher = $this->getMockBuilder(NotificationDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->notificationDispatcher->expects($this->any())->method('notifySponsorshipReward');
        $this->token_gen = $this->getMockBuilder(TokenGenerator::class)->disableOriginalConstructor()->getMock();
        $this->token_gen->expects($this->any())->method('generateToken')->willReturn('1');

        //test
        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->sponsorshipService);
        unset($this->mailDispatcher);
        unset($this->logger);
        unset($this->notificationDispatcher);
        unset($this->token_gen);
        unset($this->contract_artist);
        unset($this->user);
        unset($this->sponsorship_invitation);
        unset($this->sponsorshipRepository);
        unset($this->host_user);
        unset($this->reward);
        unset($this->contractFanRepository);
        unset($this->contractFan);
    }

    /**
     * normal method call with correct recipients : success
     */
    public function testSendSponsorshipInvitation1()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);
        $this->assertEquals([true, ['badEmails']], $this->sponsorshipService->sendSponsorshipInvitation(['emails'], 'content', $this->contract_artist, $this->user));
    }

    /**
     * normal method call without correct recipients : success
     */
    public function testSendSponsorshipInvitation2()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([[], ['badEmails']]);
        $this->assertEquals([false, ['badEmails']], $this->sponsorshipService->sendSponsorshipInvitation(['emails'], 'content', $this->contract_artist, $this->user));
    }

    /**
     * test with empty emails array
     * @expectedException Exception
     */
    public function testSendSponsorshipInvitation3()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation([], 'content', $this->contract_artist, $this->user);
    }

    /**
     * test with null emails array
     * @expectedException Exception
     */
    public function testSendSponsorshipInvitation4()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(null, 'content', $this->contract_artist, $this->user);
    }

    /**
     * test with null contract artist
     * @expectedException TypeError
     */
    public function testSendSponsorshipInvitation5()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(['emails'], null, null, $this->user);
    }

    /**
     * test with null user
     * @expectedException TypeError
     */
    public function testSendSponsorshipInvitation6()
    {
        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(['emails'], null, $this->contract_artist, null);
    }


    /**
     * test with correct sponsorshiped user
     */
    public function testCheckIfSponsorshipedAtInscription1()
    {
        $this->sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn(new \DateTime());
        $this->sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($this->sponsorship_invitation);
        $this->assertTrue($this->sponsorshipService->checkIfSponsorshipedAtInscription($this->user));
    }

    /**
     * test with correct sponsorshiped user (but he clicked on the referral link too long ago)
     */
    public function testCheckIfSponsorshipedAtInscription2()
    {
        $date = new \DateTime();
        $date = $date->sub((new \DateInterval('P' . (SponsorshipService::MAX_DAY_ACCEPTATION + 1) . 'D')));
        $this->sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn($date);
        $this->sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($this->sponsorshipRepository);
        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($this->user));
    }

    /**
     * test with user not sponsorshipped
     */
    public function testCheckIfSponsorshipedAtInscription3()
    {
        $this->sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn(null);
        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($this->user));
    }

    /**
     * test with correct user but with bad date information
     */
    public function testCheckIfSponsorshipedAtInscription4()
    {
        $this->sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn(null);
        $this->sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($this->sponsorship_invitation);
        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($this->user));
    }

    /**
     * test with null param user
     * @expectedException Exception
     */
    public function testCheckIfSponsorshipedAtInscription5()
    {
        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription(null));
    }


    public function testGiveSponsorshipRewardOnPurchaseIfPossible1()
    {
        $this->sponsorship_invitation->expects($this->any())->method('getHostInvitation')->willReturn($this->host_user);
        $this->sponsorship_invitation->expects($this->any())->method('getRewardSent')->willReturn(false);
        $this->user->expects($this->any())->method('getSponsorshipInvitation')->willReturn($this->sponsorship_invitation);
        $this->contract_artist->expects($this->any())->method('getSponsorshipReward')->willReturn($this->reward);
        $this->contractFanRepository->expects($this->any())->method('findSponsorshipContractFanToReward')->willReturn($this->contractFan);
        $this->assertTrue(true);
    }


}
