<?php
/**
 * Created by PhpStorm.
 * User: s_u_y_s_a
 * Date: 2019-05-22
 * Time: 10:32
 */

namespace Tests\AppBundle\Entity;


use AppBundle\Entity\User;
use AppBundle\Entity\YB\Membership;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\YBContractArtist;

class UserTest extends \PHPUnit_Framework_TestCase {

    /** @var User $user */
    private $user;
    /** @var Organization */
    private $org1, $org2;
    /** @var Venue */
    private $venue1;

    protected function setUp()
    {
        $this->user = new User();
        $this->user->setFirstname("John");
        $this->user->setLastname("Snow");
        $this->org1 = new Organization();
        $this->org1->setName("Night's Watch");
        $this->org2 = new Organization();
        $this->org2->setName("Winterfell's greatest");

        $this->venue1 = new Venue();
        $this->venue1->setName("Castle Black");
        $this->venue1->setOrganization($this->org1);
        $this->org1->setVenues(array($this->venue1));

        $venue2 = new Venue();
        $venue2->setName("Winterfell");
        $venue2->setOrganization($this->org2);
        $this->org2->setVenues(array($venue2));
    }

    public function testAddParticipation(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $this->user->addParticipation($participation);
        self::assertEquals(1, count($this->user->getParticipations()));

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);
        self::assertEquals(2, count($this->user->getParticipations()));
    }

    public function testRemoveParticipation(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $this->user->addParticipation($participation);
        self::assertEquals(1, count($this->user->getParticipations()));
        $this->user->removeParticipation($participation);
        self::assertEquals(0, count($this->user->getParticipations()));
    }

    public function testGetOrganizations(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $orgs = $this->user->getOrganizations();
        self::assertEquals("Night's Watch", $orgs[0]->getName());
        self::assertEquals("Winterfell's greatest", $orgs[1]->getName());
    }

    public function testGetPublicOrganizations(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $org3 = new Organization();
        $org3->setName("John Snow");
        $participation3 = new Membership();
        $participation3->setOrganization($org3);
        $this->user->addParticipation($participation3);

        $orgs = $this->user->getPublicOrganizations();
        self::assertTrue(count($orgs) === 2);
    }

    public function testIsAdminForOrganization(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(true);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $participation2->setAdmin(false);
        $this->user->addParticipation($participation2);

        self::assertTrue($this->user->isAdminForOrganization($this->org1));
        self::assertFalse($this->user->isAdminForOrganization($this->org2));
    }

    public function testSetRightForOrganization(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $org3 = new Organization();
        $org3->setName("John Snow");
        $participation3 = new Membership();
        $participation3->setOrganization($org3);
        $this->user->addParticipation($participation3);

        $this->user->setRightForOrganization($participation, true);
        self::assertTrue($this->user->isAdminForOrganization($this->org1));
    }

    public function testHasPrivateOrganization1(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $org3 = new Organization();
        $org3->setName("John Snow");
        $participation3 = new Membership();
        $participation3->setOrganization($org3);
        $this->user->addParticipation($participation3);

        self::assertTrue($this->user->hasPrivateOrganization());
    }

    public function testHasPrivateOrganization2(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        self::assertFalse($this->user->hasPrivateOrganization());
    }

    public function testGetPrivateOrganization(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $org3 = new Organization();
        $org3->setName("John Snow");
        $participation3 = new Membership();
        $participation3->setOrganization($org3);
        $this->user->addParticipation($participation3);

        $org = $this->user->getPrivateOrganization();
        self::assertEquals("John Snow", $org->getName());

        $this->user->removeParticipation($participation3);
        $org = $this->user->getPrivateOrganization();
        self::assertEquals(null, $org);
    }

    public function testGetParticipationToOrganization(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);
        $part = $this->user->getParticipationToOrganization($this->org1);
        self::assertEquals($participation, $part);
    }

    public function testGetVenuesHandled(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);

        $participation2 = new Membership();
        $participation2->setOrganization($this->org2);
        $this->user->addParticipation($participation2);

        $venues = $this->user->getVenuesHandled();
        self::assertEquals(2, count($venues));
        self::assertEquals("Winterfell", $venues[1]->getName());
    }

    public function testOwnsYBVenue(){
        $participation = new Membership();
        $participation->setOrganization($this->org1);
        $participation->setAdmin(false);
        $this->user->addParticipation($participation);
        self::assertTrue($this->user->ownsYBVenue($this->venue1));
        $v = new Venue();
        self::assertFalse($this->user->ownsYBVenue($v));
    }

    public function testOwnsYBCampaign(){
        $part = new Membership();
        $part->setOrganization($this->org1);
        $part->setMember($this->user);
        $this->user->addParticipation($part);
        $this->org1->addParticipation($part);
        $ca = new YBContractArtist();
        $ca->setTitle("Battle of the bastards");
        $ca->setOrganization($this->org1);
        $this->org1->setCampaigns(array($ca));
        self::assertTrue($this->user->ownsYBCampaign($ca));
    }

    protected function tearDown()
    {
        unset($this->user);
    }

}