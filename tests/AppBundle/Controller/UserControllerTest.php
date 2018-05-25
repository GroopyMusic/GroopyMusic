<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 24/05/2018
 * Time: 12:42
 */

use AppBundle\Controller\UserController;
use AppBundle\DataFixtures\ORM\EntitiesFixtures;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends WebTestCase
{
    static $client_s = null;
    static $metadata = null;
    static $container_s = null;
    static $em_s = null;
    private $session;
    private $container;
    private $client;
    private $em;
    private $current_user;


    protected function setUp()
    {
        if (is_null(self::$client_s)) {
            self::$client_s = static::createClient();
            self::$client_s->disableReboot();
        }
        if (is_null(self::$container_s)) {
            self::$container_s = self::$client_s->getContainer();
        }
        if (is_null(self::$em_s)) {
            self::$em_s = self::$container_s->get('doctrine')->getManager();
        }

        if (is_null(self::$metadata)) {
            self::$metadata = self::$em_s->getMetadataFactory()->getAllMetadata();
            $schemaTool = new SchemaTool(self::$em_s);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema(self::$metadata);

            $fixture = new EntitiesFixtures();
            $fixture->setContainer(self::$container_s);
            $fixture->load(self::$em_s);
        }

        $this->client = self::$client_s;
        $this->container = self::$container_s;
        $this->em = self::$em_s;

        $this->session = $this->container->get('session');
        // the firewall context defaults to the firewall name
        $firewallContext = 'main';
        $this->current_user = $this->em->getRepository('AppBundle:User')->findOneByEmail('serge.Saada@un-mute.be');
        $token = new UsernamePasswordToken($this->current_user, null, $firewallContext, array('ROLE_ADMIN'));
        $this->session->set('_security_' . $firewallContext, serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    protected function tearDown()
    {
        unset($this->client);
        unset($this->session);
        unset($this->em);
        unset($this->container);
        unset($this->current_user);
    }

    /**
     * success : display modal of user
     * just invited sponsorship, no confirmed
     * form command page
     */
    public function testDisplaySponsorshipModalAction1()
    {
        $crawler = $this->client->request('POST', '/api/display-sponsorship-invitation-modal', array('defined' => 'false'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("#sponsorship-invitations-modal")->count());
        $this->assertEquals(1, $crawler->filter("#sponsorship-modal-select")->count());
        $today = new \DateTime();
        $number_contracts = 0;
        $contracts = $this->em->getRepository('AppBundle:ContractArtist')->findAll();
        foreach ($contracts as $contract) {
            if ($contract->getPayments() != null) {
                foreach ($contract->getPayments()->toArray() as $payment) {
                    if ((($contract->getReality() != null && $contract->getReality()->getDate() > $today)
                            || ($contract->getReality() == null && $contract->getPreferences()->getDate() > $today))
                        && $contract->getFailed() == false
                        && $contract->getRefunded() == false
                        && $payment->getUser()->getId() == $this->current_user->getId()) {
                        $number_contracts++;
                    }
                }
            }
        }
        $this->assertEquals($number_contracts, $crawler->filter('#sponsorship-modal-select option')->count());
    }

    /**
     * success : display modal of user
     * just invited sponsorship, no confirmed
     * defined == null
     */
    public function testDisplaySponsorshipModalAction2()
    {
        $crawler = $this->client->request('POST', '/api/display-sponsorship-invitation-modal', array('defined' => null));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("#sponsorship-invitations-modal")->count());
        $this->assertEquals(1, $crawler->filter("#sponsorship-modal-select")->count());
        $today = new \DateTime();
        $number_contracts = 0;
        $contracts = $this->em->getRepository('AppBundle:ContractArtist')->findAll();
        foreach ($contracts as $contract) {
            if ($contract->getPayments() != null) {
                foreach ($contract->getPayments()->toArray() as $payment) {
                    if ((($contract->getReality() != null && $contract->getReality()->getDate() > $today)
                            || ($contract->getReality() == null && $contract->getPreferences()->getDate() > $today))
                        && $contract->getFailed() == false
                        && $contract->getRefunded() == false
                        && $payment->getUser()->getId() == $this->current_user->getId()) {
                        $number_contracts++;
                    }
                }
            }
        }
        $this->assertEquals($number_contracts, $crawler->filter('#sponsorship-modal-select option')->count());
    }

    /**
     * success : display modal of user
     * just invited sponsorship, no confirmed
     * form artist page
     */
    public function testDisplaySponsorshipModalAction3()
    {
        $crawler = $this->client->request('POST', '/api/display-sponsorship-invitation-modal', array('defined' => 'true'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("#sponsorship-invitations-modal")->count());
        $this->assertEquals(0, $crawler->filter("#sponsorship-modal-select")->count());
    }

    /**
     * error : user not connected
     */
    public function testDisplaySponsorshipModalAction4()
    {
        $this->container->get('security.token_storage')->setToken(null);
        $this->session->invalidate();
        $crawler = $this->client->request('POST', '/api/display-sponsorship-invitation-modal', array('defined' => 'true'));
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : send sponsorship email to one email
     */
    public function testSendSponsorshipInvitation1()
    {
        $crawler = $this->client->request('POST', '/api/send-sponsorship-invitation', array(
            'contractArtist' => '2', // to change
            'emails' => ['super.email@homail.fr'],
            'content' => 'content',
            'defined' => 'true'
        ));
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $user = $this->em->getRepository('AppBundle:User')->findOneByEmail('serge.Saada@un-mute.be');
        $this->assertEquals(2, $user->getSponsorships()->count());
        $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error : send sponsorship but contract artist not valid
     */
    public function testSendSponsorshipInvitation2()
    {
        $crawler = $this->client->request('POST', '/api/send-sponsorship-invitation', array(
            'contractArtist' => '-1', // to change
            'emails' => ['super.email@homail.fr'],
            'content' => 'content',
            'defined' => 'true'
        ));
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error : send sponsorship but user not connected
     */
    public function testSendSponsorshipInvitation3()
    {
        $this->container->get('security.token_storage')->setToken(null);
        $this->session->invalidate();
        $crawler = $this->client->request('POST', '/api/send-sponsorship-invitation', array(
            'contractArtist' => '-1', // to change
            'emails' => ['super.email@homail.fr'],
            'content' => 'content',
            'defined' => 'true'
        ));
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error : send email not valid
     */
    public function testSendSponsorshipInvitation4()
    {
        $crawler = $this->client->request('POST', '/api/send-sponsorship-invitation', array(
            'contractArtist' => '-1', // to change
            'emails' => 'super.email@homail.fr',
            'content' => 'content',
            'defined' => 'true'
        ));
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : user diplay rewards
     */
    public function testRewardsAction1()
    {
        $crawler = $this->client->request('GET', '/rewards');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1,$crawler->filter('#rewards-table tr')->count());
        $link_text = $crawler->filter('.button-display-reward')->first()->text();
        $this->client->click($crawler->selectLink($link_text)->link());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1,$crawler->filter('#reward-information')->count());

    }


}
