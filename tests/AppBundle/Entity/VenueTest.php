<?php
/**
 * Created by PhpStorm.
 * User: s_u_y_s_a
 * Date: 2019-05-21
 * Time: 16:30
 */

namespace Tests\AppBundle\Entity;


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

    protected function tearDown()
    {
        unset($this->venue);
    }

}