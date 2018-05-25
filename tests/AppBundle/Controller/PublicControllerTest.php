<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 16/05/2018
 * Time: 12:13
 */

use AppBundle\DataFixtures\ORM\EntitiesFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PublicControllerTest extends WebTestCase
{
    static $client_s = null;
    static $metadata = null;
    static $container_s = null;
    static $em_s = null;
    private $session;
    private $container;
    private $client;
    private $em;


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
        $user = $this->em->getRepository('AppBundle:User')->findOneByEmail('admin@un-mute.be');
        $token = new UsernamePasswordToken($user, null, $firewallContext, array('ROLE_ADMIN'));
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
    }


    /**
     * Success :  Token valide, sponsorship valid, new user
     */
    public function testSponsorshipLinkAction1()
    {
        $token = 'aZlb1S6vxD9hJglmU9ECYBBct639hT9Q';
        $today = new \DateTime();
        $crawler = $this->client->request('GET', '/sponsorship-link-token-' . $token);
        $sponsorship = $this->em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipInvitationByToken($token);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode()); // is redirect
        $this->assertTrue($this->client->getResponse()->isRedirect('/evenements/' . $sponsorship->getContractArtist()->getId() . '-concert-de')); // test redirect page
        $this->assertEquals($today->format('Y-m-d'), $sponsorship->getLastDateAcceptation()->format('Y-m-d')); // test date

        $this->client->followRedirect();
        $this->assertTrue(key_exists('notice', $this->session->getBag('flashes')->peekAll()));// test if flashbag
        $this->assertEquals(1, count($this->session->getBag('flashes')->peekAll()['notice']));

        $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error :  token invalide
     */
    public function testSponsorshipLinkAction2()
    {
        $token = 'invalide token';
        $crawler = $this->client->request('GET', '/sponsorship-link-token-' . $token);

        $this->assertEquals(302, $this->client->getResponse()->getStatusCode()); // is redirect
        $this->assertTrue($this->client->getResponse()->isRedirect('/')); // test redirect home page
        $this->assertTrue(key_exists('error', $this->session->getBag('flashes')->peekAll()));// test if flashbag
        $this->assertEquals(1, count($this->session->getBag('flashes')->peekAll()['error']));
        $this->client->followRedirect();
        //dump($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

}
