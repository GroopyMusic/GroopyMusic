<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 22/05/2018
 * Time: 12:28
 */

use AppBundle\Controller\Select2Controller;
use AppBundle\DataFixtures\ORM\EntitiesFixtures;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Select2ControllerTest extends WebTestCase
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
     * Success : success gat all not deleted and visible user : SeeUSoon
     */
    public function testArtistsAction1()
    {
        $crawler = $this->client->request('GET', '/select2/artists', array('q' => array('artistname' => '')));
        $artists = $this->em->getRepository('AppBundle:Artist')->findAll();
        $response = [];
        foreach ($artists as $artist) {
            if ($artist->getDeleted() == false && $artist->getVisible() == true) {
                $response[] = array(
                    'id' => $artist->getId(),
                    'text' => $artist->getArtistname(),
                );
            }
        }
        $this->assertEquals(json_encode($response), $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : success get SeeUSoon woth search
     */
    public function testArtistsAction2()
    {
        $crawler = $this->client->request('GET', '/select2/artists', array('q' => array('artistname' => 'SeeUsoon')));
        $this->assertEquals('[{"id":1,"text":"SeeUsoon"}]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : success get 0 artist
     */
    public function testArtistsAction3()
    {
        $crawler = $this->client->request('GET', '/select2/artists', array('q' => array('artistname' => 'jfrhgtguuhgti')));
        $this->assertEquals('[]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Error param q don't have artistname as key
     */
    public function testArtistsAction4()
    {
        $crawler = $this->client->request('GET', '/select2/artists', array('q' => array('')));
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }

    /**
     * success find all not deleted user
     */
    public function testUsersAction1()
    {
        $crawler = $this->client->request('GET', '/select2/users', array('q' => ''));
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        $response = [];
        foreach ($users as $user) {
            if ($user->getDeleted() == false) {
                $response[] = array(
                    'id' => $user->getId(),
                    'text' => $user->getDisplayName()
                );
            }

        }
        $this->assertEquals(json_encode($response), $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : get one user Jhon Doe
     */
    public function testUsersAction2()
    {
        $crawler = $this->client->request('GET', '/select2/users', array('q' => 'John'));
        $this->assertEquals('[{"id":1,"text":"John Doe"}]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : success get 0 artist
     */
    public function testUsersAction3()
    {
        $crawler = $this->client->request('GET', '/select2/users', array('q' => 'frueuhgthrtgezybrzgbeugbug'));
        $this->assertEquals('[]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error param null
     */
    public function testUsersAction4()
    {
        $crawler = $this->client->request('GET', '/select2/users', array('q' => null));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * success find all not deleted user
     */
    public function testNewsletterUsersAction1()
    {
        $crawler = $this->client->request('GET', '/select2/newsletter_users', array('q' => ''));
        $users = $this->em->getRepository('AppBundle:User')->findAll();
        $response = [];
        foreach ($users as $user) {
            if ($user->getDeleted() == false && $user->getNewsletter() == true) {
                $response[] = array(
                    'id' => $user->getId(),
                    'text' => $user->getDisplayName()
                );
            }

        }
        $this->assertEquals(json_encode($response), $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : get one user Jhon Doe
     */
    public function testNewsletterUsersAction2()
    {
        $crawler = $this->client->request('GET', '/select2/newsletter_users', array('q' => 'John'));
        $this->assertEquals('[{"id":1,"text":"John Doe"}]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : success get 0 artist
     */
    public function testNewsletterUsersAction3()
    {
        $crawler = $this->client->request('GET', '/select2/newsletter_users', array('q' => 'frueuhgthrtgezybrzgbeugbug'));
        $this->assertEquals('[]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error param null
     */
    public function testNewsletterUsersAction4()
    {
        $crawler = $this->client->request('GET', '/select2/newsletter_users', array('q' => null));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * success find all contract artist possible
     */
    public function testContractArtistsAction1()
    {
        $crawler = $this->client->request('GET', '/select2/contractArtists', array('q' => ''));
        $contracts = $this->em->getRepository('AppBundle:ContractArtist')->findAll();
        $response = [];
        foreach ($contracts as $contract) {
            $response[] = array(
                'id' => $contract->getId(),
                'text' => $contract->__toString(), // error null
            );
        }
        $this->assertEquals(json_encode($response), $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * success find one contract artist possible
     */
    public function testContractArtistsAction2()
    {
        $crawler = $this->client->request('GET', '/select2/contractArtists', array('q' => 'SeeUsoon'));
        $this->assertEquals('[{"id":1,"text":null}]', $this->client->getResponse()->getContent());//error null
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Success : success get 0 artist
     */
    public function testContractArtistsAction3()
    {
        $crawler = $this->client->request('GET', '/select2/contractArtists', array('q' => 'frueuhgthrtgezybrzgbeugbug'));
        $this->assertEquals('[]', $this->client->getResponse()->getContent());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * error param null
     */
    public function testContractArtistsAction4()
    {
        $crawler = $this->client->request('GET', '/select2/contractArtists', array('q' => null));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
