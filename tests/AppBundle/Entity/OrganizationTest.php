<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Membership;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\OrganizationJoinRequest;
use AppBundle\Entity\YB\Venue;

class OrganizationTest extends \PHPUnit_Framework_TestCase {

    /** @var Organization $org */
    private $org;

    protected function setUp(){
        $this->org = new Organization();
    }

    public function testAddParticipation(){
        self::assertEquals(0, count($this->org->getParticipations()));
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $this->org->addParticipation($membership);
        self::assertEquals(1, count($this->org->getParticipations()));
    }

    public function testRemoveParticipation(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $this->org->addParticipation($membership);
        self::assertEquals(1, count($this->org->getParticipations()));
        $this->org->removeParticipation($membership);
        self::assertEquals(0, count($this->org->getParticipations()));
    }

    public function testGetMembers(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $this->org->addParticipation($membership2);

        $members = $this->org->getMembers();
        self::assertEquals(3, count($members));
        self::assertEquals("Roland", $members[1]->getUsername());
    }

    public function testHasOnlyOneMember1(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $this->org->addParticipation($membership2);

        self::assertFalse($this->org->hasOnlyOneMember());
    }

    public function testHasOnlyOneMember2(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        self::assertTrue($this->org->hasOnlyOneMember());
    }

    public function testHasAtLeastOneAdminLeft1(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setAdmin(true);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $this->org->addParticipation($membership2);

        self::assertTrue($this->org->hasAtLeastOneAdminLeft($user1));
    }

    public function testHasAtLeastOneAdminLeft2(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $this->org->addParticipation($membership2);

        self::assertFalse($this->org->hasAtLeastOneAdminLeft($user));
    }

    public function testHasMember1(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();

        self::assertFalse($this->org->hasMember($user2));
    }

    public function testHasMember2(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setMember($user);
        $this->org->addParticipation($membership);

        $user1 = new User();
        $user1->setUsername("Roland");
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $this->org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $this->org->addParticipation($membership2);

        self::assertTrue($this->org->hasMember($user2));
    }

    public function testHasPendingRequest1(){
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setMember($user);
        $this->org->addParticipation($membership);
        $requests = [];
        $requests[] = new OrganizationJoinRequest($user, "test@test.be", $this->org);
        $this->org->setJoinOrganizationRequest($requests);
        self::assertTrue($this->org->hasPendingRequest());
    }

    public function testHasPendingRequest2(){
        self::assertFalse($this->org->hasPendingRequest());
    }

    public function testHandleVenue(){
        $venue1 = new Venue();
        $venue1->setName("AB");
        $venue2 = new Venue();
        $venue2->setName("Palais 12");
        $venue3 = new Venue();
        $venue3->setName("Forest National");
        $venue4 = new Venue();
        $venue4->setName("Botanique");
        $venues = [];
        $venues[] = $venue1;
        $venues[] = $venue2;
        $venues[] = $venue3;
        $venues[] = $venue4;
        $this->org->setVenues($venues);
        $venue5 = new Venue();
        $venue5->setName("Sportpaleis");
        self::assertTrue($this->org->handleVenue($venue3));
        self::assertTrue($this->org->handleVenue($venue1));
        self::assertFalse($this->org->handleVenue($venue5));
    }

    protected function tearDown(){
        unset($this->org);
    }

}