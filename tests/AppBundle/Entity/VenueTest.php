<?php
/**
 * Created by PhpStorm.
 * User: s_u_y_s_a
 * Date: 2019-05-21
 * Time: 16:30
 */

namespace Tests\AppBundle\Entity;


use AppBundle\Entity\User;
use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\Membership;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\VenueConfig;

class VenueTest extends \PHPUnit_Framework_TestCase {

    /** @var Venue $venue */
    private $venue;

    protected function setUp()
    {
        $this->venue = new Venue();
    }

    public function testCreateDefaultConfig(){
        self::assertEquals(0, count($this->venue->getConfigurations()));
        $this->venue->createDefaultConfig();
        self::assertEquals(1, count($this->venue->getConfigurations()));
    }

    public function testGetNotDefaultConfig(){
        $cfg1 = new VenueConfig();
        $cfg2 = new VenueConfig();
        $cfg3 = new VenueConfig();
        $cfg4 = new VenueConfig();
        $this->venue->addConfiguration($cfg1);
        $this->venue->addConfiguration($cfg2);
        $this->venue->addConfiguration($cfg3);
        $this->venue->addConfiguration($cfg4);
        $this->venue->createDefaultConfig();
        $nonDefault = $this->venue->getNotDefaultConfig();
        self::assertEquals(5, count($this->venue->getConfigurations()));
        self::assertEquals(4, count($nonDefault));
    }

    public function testGetHandlers(){
        $org = new Organization();
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setAdmin(true);
        $org->addParticipation($membership);

        $user1 = new User();
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $org->addParticipation($membership2);

        $this->venue->setOrganization($org);
        self::assertEquals(3, count($this->venue->getHandlers()));
    }

    public function testIsHandledByUser1(){
        $org = new Organization();
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setAdmin(true);
        $org->addParticipation($membership);

        $user1 = new User();
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $org->addParticipation($membership2);

        $this->venue->setOrganization($org);
        self::assertTrue($this->venue->isHandledByUser($user));
    }

    public function testIsHandledByUser2(){
        $org = new Organization();
        $user = new User();
        $membership = new Membership();
        $membership->setMember($user);
        $membership->setAdmin(true);
        $org->addParticipation($membership);

        $user1 = new User();
        $membership1 = new Membership();
        $membership1->setMember($user1);
        $org->addParticipation($membership1);

        $user2 = new User();
        $membership2 = new Membership();
        $membership2->setMember($user2);
        $org->addParticipation($membership2);

        $user3 = new User();

        $this->venue->setOrganization($org);
        self::assertFalse($this->venue->isHandledByUser($user3));
    }

    public function testGenerateRows(){
        $cfg1 = new VenueConfig();
        $block11 = new Block();
        $block11->setNbRows(10);
        $block11->setNbSeatsPerRow(15); // bloc de 150 places
        $block11->setRowLabel(1); // row letter : A -> Z
        $block11->setSeatLabel(2); // row number : 1 -> 30
        $cfg1->addBlock($block11);
        $block12 = new Block();
        $block12->setNbRows(5);
        $block12->setNbSeatsPerRow(10); // bloc de 50 places
        $block12->setRowLabel(1); // row letter : A -> Z
        $block12->setSeatLabel(2); // row number : 1 -> 30
        $cfg1->addBlock($block12);
        $cfg2 = new VenueConfig();
        $block21 = new Block();
        $block21->setNbRows(15);
        $block21->setNbSeatsPerRow(10); // bloc de 150 places
        $block21->setRowLabel(1); // row letter : A -> Z
        $block21->setSeatLabel(2); // row number : 1 -> 30
        $cfg2->addBlock($block21);// row number : 1 -> 30
        $this->venue->addConfiguration($cfg1);
        $this->venue->addConfiguration($cfg2);
        $this->venue->generateRows();

        self::assertEquals(10, count($block11->getRows()));
        $row = $block11->getRows()[0];
        self::assertEquals(15, count($row->getSeats()));

        self::assertEquals(5, count($block12->getRows()));
        $row = $block12->getRows()[0];
        self::assertEquals(10, count($row->getSeats()));

        self::assertEquals(15, count($block21->getRows()));
        $row = $block21->getRows()[0];
        self::assertEquals(10, count($row->getSeats()));
    }

    public function testIsOnlyFreeSeating1(){
        $cfg1 = new VenueConfig();
        $cfg1->setOnlyStandup(true);
        $cfg2 = new VenueConfig();
        $cfg2->setOnlyStandup(true);
        $cfg3 = new VenueConfig();
        $cfg3->setHasFreeSeatingPolicy(true);
        $cfg4 = new VenueConfig();
        $cfg4->setHasFreeSeatingPolicy(true);
        $this->venue->addConfiguration($cfg1);
        $this->venue->addConfiguration($cfg2);
        $this->venue->addConfiguration($cfg3);
        $this->venue->addConfiguration($cfg4);
        self::assertTrue($this->venue->isOnlyFreeSeating());
    }

    public function testIsOnlyFreeSeating2(){
        $cfg1 = new VenueConfig();
        $cfg1->setOnlyStandup(true);
        $cfg2 = new VenueConfig();
        $cfg2->setOnlyStandup(true);
        $cfg3 = new VenueConfig();
        $cfg3->setHasFreeSeatingPolicy(true);
        $cfg4 = new VenueConfig();
        $this->venue->addConfiguration($cfg1);
        $this->venue->addConfiguration($cfg2);
        $this->venue->addConfiguration($cfg3);
        $this->venue->addConfiguration($cfg4);
        self::assertFalse($this->venue->isOnlyFreeSeating());
    }

    protected function tearDown()
    {
        unset($this->venue);
    }

}