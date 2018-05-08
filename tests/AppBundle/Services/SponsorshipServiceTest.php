<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 08/05/2018
 * Time: 11:35
 */

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\User;
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
    private $sponsorshipService;
    private $manager;
    private $logger;
    private $mailDispatcher;
    private $notificationDispatcher;
    private $token_gen;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();

        $this->mailDispatcher = $this->getMockBuilder(MailDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->mailDispatcher->expects($this->any())->method('sendSponsorshipInvitationEmail');

        $this->notificationDispatcher = $this->getMockBuilder(NotificationDispatcher::class)->disableOriginalConstructor()->getMock();

        $this->token_gen = $this->getMockBuilder(TokenGenerator::class)->disableOriginalConstructor()->getMock();
        $this->token_gen->expects($this->any())->method('generateToken')->willReturn('1');

    }

    protected function tearDown()
    {
        unset($this->sponsorshipService);
        unset($this->mailDispatcher);
        unset($this->logger);
        unset($this->notificationDispatcher);
        unset($this->token_gen);
    }

    /**
     * normal method call with correct recipients : success
     */
    public function testSendSponsorshipInvitation1()
    {
        $contract_artist = $this->getMockBuilder(ContractArtist::class)->disableOriginalConstructor()->getMock();
        $contract_artist->expects($this->any())->method('getId')->willReturn(1);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->assertEquals([true, ['badEmails']], $this->sponsorshipService->sendSponsorshipInvitation(['emails'], 'content', $contract_artist, $user));
    }

    /**
     * normal method call without correct recipients : success
     */
    public function testSendSponsorshipInvitation2()
    {
        $contract_artist = $this->getMockBuilder('AppBundle\Entity\ContractArtist')->disableOriginalConstructor()->getMock();
        $contract_artist->expects($this->any())->method('getId')->willReturn(1);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([[], ['badEmails']]);

        $this->assertEquals([false, ['badEmails']], $this->sponsorshipService->sendSponsorshipInvitation(['emails'], 'content', $contract_artist, $user));
    }

    /**
     * test with empty emails array
     * @expectedException Exception
     */
    public function testSendSponsorshipInvitation3()
    {
        $contract_artist = $this->getMockBuilder(ContractArtist::class)->disableOriginalConstructor()->getMock();
        $contract_artist->expects($this->any())->method('getId')->willReturn(1);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation([], 'content', $contract_artist, $user);
    }

    /**
     * test with null emails array
     * @expectedException Exception
     */
    public function testSendSponsorshipInvitation4()
    {
        $contract_artist = $this->getMockBuilder(ContractArtist::class)->disableOriginalConstructor()->getMock();
        $contract_artist->expects($this->any())->method('getId')->willReturn(1);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(null, 'content', $contract_artist, $user);
    }

    /**
     * test with null contract artist
     * @expectedException TypeError
     */
    public function testSendSponsorshipInvitation5()
    {
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(['emails'], null, null, $user);
    }

    /**
     * test with null user
     * @expectedException TypeError
     */
    public function testSendSponsorshipInvitation6()
    {
        $contract_artist = $this->getMockBuilder(ContractArtist::class)->disableOriginalConstructor()->getMock();
        $contract_artist->expects($this->any())->method('getId')->willReturn(1);

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->sponsorshipService->expects($this->any())
            ->method('verifyEmails')
            ->willReturn([['goodEmails'], ['badEmails']]);

        $this->sponsorshipService->sendSponsorshipInvitation(['emails'], null, $contract_artist, null);
    }


    /**
     * test with correct sponsorshiped user
     */
    public function testCheckIfSponsorshipedAtInscription1()
    {
        $sponsorship_invitation = $this->getMockBuilder(SponsorshipInvitation::class)->disableOriginalConstructor()->getMock();
        $sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn(new \DateTime());
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository = $this->getMockBuilder(SponsorshipInvitationRepository::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($sponsorship_invitation);
        $this->manager->expects($this->any())->method('getRepository')->willReturn($sponsorshipRepository);

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->assertTrue($this->sponsorshipService->checkIfSponsorshipedAtInscription($user));
    }

    /**
     * test with correct sponsorshiped user (but he clicked on the referral link too long ago)
     */
    public function testCheckIfSponsorshipedAtInscription2()
    {
        $sponsorship_invitation = $this->getMockBuilder(SponsorshipInvitation::class)->disableOriginalConstructor()->getMock();
        $date = new \DateTime();
        $date = $date->sub((new \DateInterval('P' . (SponsorshipService::MAX_DAY_ACCEPTATION + 1) . 'D')));
        $sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn($date);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository = $this->getMockBuilder(SponsorshipInvitationRepository::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($sponsorship_invitation);
        $this->manager->expects($this->any())->method('getRepository')->willReturn($sponsorshipRepository);

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($user));
    }

    /**
     * test with user not sponsorshipped
     */
    public function testCheckIfSponsorshipedAtInscription3()
    {
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository = $this->getMockBuilder(SponsorshipInvitationRepository::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn(null);
        $this->manager->expects($this->any())->method('getRepository')->willReturn($sponsorshipRepository);

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($user));
    }

    /**
     * test with correct user but with bad date information
     */
    public function testCheckIfSponsorshipedAtInscription4()
    {
        $sponsorship_invitation = $this->getMockBuilder(SponsorshipInvitation::class)->disableOriginalConstructor()->getMock();
        $sponsorship_invitation->expects($this->any())->method('getLastDateAcceptation')->willReturn(null);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository = $this->getMockBuilder(SponsorshipInvitationRepository::class)->disableOriginalConstructor()->getMock();
        $sponsorshipRepository->expects($this->any())->method('getSponsorshipInvitationByMail')->willReturn($sponsorship_invitation);
        $this->manager->expects($this->any())->method('getRepository')->willReturn($sponsorshipRepository);

        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription($user));
    }

    /**
     * test with null param user
     * @expectedException Exception
     */
    public function testCheckIfSponsorshipedAtInscription5()
    {
        $this->sponsorshipService = $this->getMockBuilder(SponsorshipService::class)
            ->setConstructorArgs(array($this->mailDispatcher, $this->manager, $this->logger, $this->token_gen, $this->notificationDispatcher))
            ->setMethods(array('verifyEmails'))
            ->getMock();

        $this->assertFalse($this->sponsorshipService->checkIfSponsorshipedAtInscription(null));
    }


    public function testGiveSponsorshipRewardOnPurchaseIfPossible1()
    {
        $host_user = $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $sponsorship_invitation = $this->getMockBuilder(SponsorshipInvitation::class)->disableOriginalConstructor()->getMock();
        $sponsorship_invitation->expects($this->any())->method('getHostInvitation')->willReturn($host_user);
        $sponsorship_invitation->expects($this->any())->method('getRewardSent')->willReturn(false);
        $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
        $user->expects($this->any())->method('getSponsorshipInvitation')->willReturn($sponsorship_invitation);
    }


}
