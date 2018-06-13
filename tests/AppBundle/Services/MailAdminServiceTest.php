<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/05/2018
 * Time: 10:11
 */

use AppBundle\Entity\Artist;
use AppBundle\Entity\User;
use AppBundle\Services\MailAdminService;
use AppBundle\Services\MailDispatcher;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MailAdminServiceTest extends TestCase
{
    private $mailAdminService;
    private $manager;
    private $logger;
    private $mailDisptacher;
    private $translator;
    private $artistUserRepository;
    private $userRepository;
    private $contractArtistRepository;

    private $user;
    private $co_artist_user;
    private $user_artist;
    private $artist;
    private $contract_artist;

    protected function setUp()
    {
        //repository
        $this->artistUserRepository = $this->getMockBuilder(\AppBundle\Repository\Artist_UserRepository::class)->disableOriginalConstructor()->getMock();
        $this->userRepository = $this->getMockBuilder(\AppBundle\Repository\UserRepository::class)->disableOriginalConstructor()->getMock();
        $this->contractArtistRepository = $this->getMockBuilder(\AppBundle\Repository\ContractArtistRepository::class)->disableOriginalConstructor()->getMock();

        //entity
        $this->user = new User();
        $this->setIdWithReflectionClass(User::class, $this->user, 1);
        $this->co_artist_user = new User();
        $this->setIdWithReflectionClass(User::class, $this->co_artist_user, 2);
        $this->user_artist = new \AppBundle\Entity\Artist_User();
        $this->artist = new Artist(new \AppBundle\Entity\Phase());
        $this->contract_artist = new \AppBundle\Entity\ContractArtist();

        //service
        $this->manager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('persist');
        $this->manager->expects($this->any())->method('flush');
        $repositories = array(
            array("AppBundle:Artist_User", $this->artistUserRepository),
            array("AppBundle:User", $this->userRepository),
            array("AppBundle:ContractArtist", $this->contractArtistRepository)
        );
        $this->manager->expects($this->any())->method('getRepository')->will($this->returnValueMap($repositories));

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->mailDisptacher = $this->getMockBuilder(MailDispatcher::class)->disableOriginalConstructor()->getMock();
        $this->translator = $this->getMockBuilder(\Symfony\Bundle\FrameworkBundle\Translation\Translator::class)->disableOriginalConstructor()->getMock();

        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(null)
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->mailAdminService);
        unset($this->mailDisptacher);
        unset($this->translator);
        unset($this->manager);
        unset($this->logger);
        unset($this->artistUserRepository);
        unset($this->user_artist);
        unset($this->user);
        unset($this->userRepository);
        unset($this->artist);
        unset($this->contract_artist);
    }

    /**
     * Success : Retrieve members of one ownerArtist
     */
    public function testFillArtistOwnersArray1()
    {
        $this->user->setEmail("user@email.com");
        $this->user_artist->setUser($this->user);
        $this->artist->addArtistsUser($this->user_artist);
        $result = [
            ['email' => "user@email.com", 'id' => 1]
        ];
        $this->artistUserRepository->expects($this->any())->method('getArtistOwners')->willReturn([$this->user_artist]);
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray([1]));
    }

    /**
     * Success : Retrieve members with several ownerArtist
     */
    public function testFillArtistOwnersArray2()
    {
        $this->user->setEmail("user@email.com");
        $this->user_artist->setUser($this->user);
        $this->artist->addArtistsUser($this->user_artist);
        $result = [
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1]
        ];
        $this->artistUserRepository->expects($this->any())->method('getArtistOwners')->willReturn([$this->user_artist, $this->user_artist, $this->user_artist]);
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray([1]));
    }

    /**
     * success : param artist empty
     */
    public function testFillArtistOwnersArray3()
    {
        $result = [];
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : param artist null
     */
    public function testFillArtistOwnersArray4()
    {
        $result = [];
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray(null));
    }

    /**
     * success : repo return empty array
     */
    public function testFillArtistOwnersArray5()
    {
        $result = [];
        $this->artistUserRepository->expects($this->any())->method('getArtistOwners')->willReturn([]);
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray([1]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : repo return null
     */
    public function testFillArtistOwnersArray6()
    {
        $result = [];
        $this->artistUserRepository->expects($this->any())->method('getArtistOwners')->willReturn(null);
        $this->assertEquals($result, $this->mailAdminService->fillArtistOwnersArray([1]));
    }

    /**
     * Success : Retrieve one participant of one contract artist
     */
    public function testFillParticipantsArray1()
    {
        $this->user->setEmail("user@email.com");;
        $result = [
            ['email' => "user@email.com", 'id' => 1]
        ];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([$this->user]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([1]));
    }

    /**
     * Success : Retrieve one participant of several contract artist
     */
    public function testFillParticipantsArray2()
    {
        $this->user->setEmail("user@email.com");;
        $result = [
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
        ];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([$this->user]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([1, 2]));
    }

    /**
     * Success : Retrieve participants of several contract artist
     */
    public function testFillParticipantsArray3()
    {
        $this->user->setEmail("user@email.com");;
        $result = [
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
        ];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([$this->user, $this->user]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([1, 2]));
    }

    /**
     * Success : param contracrt artist empty
     */
    public function testFillParticipantsArray4()
    {
        $result = [];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : param contracrt artist null
     */
    public function testFillParticipantsArray5()
    {
        $result = [];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray(null));
    }

    /**
     * success : repo return empty
     */
    public function testFillParticipantsArray6()
    {
        $result = [];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn([]);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([1]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * success : repo return null
     */
    public function testFillParticipantsArray7()
    {
        $result = [];
        $this->userRepository->expects($this->any())->method('getParticipants')->willReturn(null);
        $this->assertEquals($result, $this->mailAdminService->fillParticipantsArray([1]));
    }

    /**
     * success : event  with an artist and no co artist
     */
    public function testFillArtistParticipantsArray1()
    {
        $this->user->setEmail("user@email.com");
        $this->user_artist->setUser($this->user);
        $this->artist->addArtistsUser($this->user_artist);
        $this->contract_artist->setArtist($this->artist);
        $result = [
            ['email' => "user@email.com", 'id' => 1]
        ];
        $this->contractArtistRepository->expects($this->any())->method('getArtistParticipants')->willReturn($this->contract_artist);
        $this->assertEquals($result, $this->mailAdminService->fillArtistParticipantsArray([1]));
    }

    /**
     * success : event  with an artist and co artist
     */
    public function testFillArtistParticipantsArray2()
    {
        $this->user->setEmail("user@email.com");
        $this->user_artist->setUser($this->user);
        $this->artist->addArtistsUser($this->user_artist);
        $this->contract_artist->setArtist($this->artist);
        $this->contract_artist->addCoArtist($this->artist);
        $result = [
            ['email' => "user@email.com", 'id' => 1],
            ['email' => "user@email.com", 'id' => 1],
        ];
        $this->contractArtistRepository->expects($this->any())->method('getArtistParticipants')->willReturn($this->contract_artist);
        $this->assertEquals($result, $this->mailAdminService->fillArtistParticipantsArray([1]));
    }

    /**
     * success : param contract artist empty
     */
    public function testFillArtistParticipantsArray3()
    {
        $result = [];
        $this->contractArtistRepository->expects($this->any())->method('getArtistParticipants')->willReturn($this->contract_artist);
        $this->assertEquals($result, $this->mailAdminService->fillArtistParticipantsArray([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : param contract artist null
     */
    public function testFillArtistParticipantsArray4()
    {
        $result = [];
        $this->contractArtistRepository->expects($this->any())->method('getArtistParticipants')->willReturn($this->contract_artist);
        $this->assertEquals($result, $this->mailAdminService->fillArtistParticipantsArray(null));
    }

    /**
     * @expectedException Error
     * error : repo return null
     */
    public function testFillArtistParticipantsArray5()
    {
        $result = [];
        $this->contractArtistRepository->expects($this->any())->method('getArtistParticipants')->willReturn(null);
        $this->assertEquals($result, $this->mailAdminService->fillArtistParticipantsArray([1]));
    }

    /**
     * @dataProvider getSimpleEmailsProvider
     */
    public function testGetSimpleEmails1($recipients, $result)
    {
        $this->assertEquals($result, $this->mailAdminService->getSimpleEmails($recipients));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * Error : recipients = null
     */
    public function testGetSimpleEmails2()
    {
        $this->assertEquals([], $this->mailAdminService->getSimpleEmails(null));
    }

    /**
     * success : array unique on mulptiple same user table
     */
    public function testGetUsersSummary1()
    {
        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(array('addAdminToRecipients', 'constructArrayRecipients'))
            ->getMock();
        $this->mailAdminService->expects($this->any())->method('addAdminToRecipients')->willReturn([$this->user, $this->user, $this->user]);
        $this->mailAdminService->expects($this->any())->method('constructArrayRecipients')->willReturn([]);
        $this->assertEquals([$this->user], $this->mailAdminService->getUsersSummary(null));
    }

    /**
     * success : array param empty
     */
    public function testGetUsersSummary2()
    {
        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(array('addAdminToRecipients'))
            ->getMock();
        $this->mailAdminService->expects($this->any())->method('addAdminToRecipients')->willReturn([$this->user, $this->user, $this->user]);
        $this->assertEquals([$this->user], $this->mailAdminService->getUsersSummary([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : array param null
     */
    public function testGetUsersSummary3()
    {
        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(array('addAdminToRecipients'))
            ->getMock();
        $this->mailAdminService->expects($this->any())->method('addAdminToRecipients')->willReturn([$this->user, $this->user, $this->user]);
        $this->assertEquals([$this->user], $this->mailAdminService->getUsersSummary(null));
    }

    /**
     * success : array unique on empty
     */
    public function testGetUsersSummary4()
    {
        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(array('addAdminToRecipients', 'constructArrayRecipients'))
            ->getMock();
        $this->mailAdminService->expects($this->any())->method('addAdminToRecipients')->willReturn([]);
        $this->mailAdminService->expects($this->any())->method('constructArrayRecipients')->willReturn([]);
        $this->assertEquals([], $this->mailAdminService->getUsersSummary([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * success : array unique on null
     */
    public function testGetUsersSummary5()
    {
        $this->mailAdminService = $this->getMockBuilder(MailAdminService::class)
            ->setConstructorArgs(array($this->manager, $this->logger, $this->mailDisptacher, $this->translator))
            ->setMethods(array('addAdminToRecipients', 'constructArrayRecipients'))
            ->getMock();
        $this->mailAdminService->expects($this->any())->method('addAdminToRecipients')->willReturn(null);
        $this->mailAdminService->expects($this->any())->method('constructArrayRecipients')->willReturn([]);
        $this->assertEquals([], $this->mailAdminService->getUsersSummary([]));
    }

    /**
     * success : all users
     */
    public function testConstructArrayRecipients1()
    {
        $user1 = new User();
        $user2 = new User();
        $user3 = new User();
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($this->user);
        $recipients = [
            "users" => 'all'
        ];
        $result = [$user1,$user2,$user3];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * success : all newsletter users
     */
    public function testConstructArrayRecipients2()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($this->user);
        $recipients = [
            "newsletter_users" => 'all'
        ];
        $result = [$user1,$user2];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * success : all users with other user
     */
    public function testConstructArrayRecipients3()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "users" => 'all',
            'artist' => [1]
        ];
        $result = [$user1,$user2,$user3,$user1];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * success : 2 user
     */
    public function testConstructArrayRecipients4()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "users" => [1]
        ];
        $result = [$user1];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * success : recipients empty
     */
    public function testConstructArrayRecipients5()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients([]));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : recipients null
     */
    public function testConstructArrayRecipients6()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients(null));
    }

    /**
     * success : all user repo return empty
     */
    public function testConstructArrayRecipients7()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "users" => 'all'
        ];
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : all user repo return null
     */
    public function testConstructArrayRecipients8()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn(null);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "users" => 'all'
        ];
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * success : newsletter user repo return empty
     */
    public function testConstructArrayRecipients9()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([]);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "newsletter_users" => 'all'
        ];
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     * error : newsletter user repo return null
     */
    public function testConstructArrayRecipients10()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn(null);
        $this->userRepository->expects($this->any())->method('find')->willReturn($user1);
        $recipients = [
            "newsletter_users" => 'all'
        ];
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }

    /**
     * error : find user return null
     */
    public function testConstructArrayRecipients11()
    {
        $user1 = new User();//newsletter
        $user2 = new User();//newsletter
        $user3 = new User();//not newsletter
        $this->userRepository->expects($this->any())->method('findUsersNotDeletedForSelect')->willReturn([$user1,$user2,$user3]);
        $this->userRepository->expects($this->any())->method('findNewsletterUsersNotDeletedForSelect')->willReturn([$user1,$user2]);
        $this->userRepository->expects($this->any())->method('find')->willReturn(null);
        $recipients = [
            "newsletter_users" => [1]
        ];
        $result = [];
        $this->assertEquals($result,$this->mailAdminService->constructArrayRecipients($recipients));
    }


    public function getSimpleEmailsProvider()
    {
        return [
            [['emails_input' => ['mail1', 'mail2', 'mail3']], ['mail1', 'mail2', 'mail3']], //3 different email
            [['emails_input' => ['mail1', 'mail1', 'mail3']], ['mail1', 'mail3']], // 2 same mail + 1 different
            [['emails_input' => ['mail1', '', 'mail3']], ['mail1', 'mail3']], //2 mail + 1 mail empty
            [['emails_input' => ['mail1', '   ', 'mail3']], ['mail1', 'mail3']], // 2 mail + 1 mail with just space
            [['emails_input' => []], []], // empty with key exist
            [[], []], // empty without key
        ];
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
