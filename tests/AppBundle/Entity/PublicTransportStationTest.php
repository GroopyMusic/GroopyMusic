<?php

namespace Tests\AppBundle\Entity;


use AppBundle\Entity\YB\PublicTransportStation;

class PublicTransportStationTest extends \PHPUnit_Framework_TestCase {

    /** @var PublicTransportStation */
    private $station;

    protected function setUp(){
        $this->station = new PublicTransportStation("Etterbeek", 50, 4, PublicTransportStation::SNCB, 1.123);
    }

    public function testTimeToWalk(){
        self::assertEquals("13min", $this->station->timeToWalk());
        $this->station->setDistance(0.1);
        self::assertEquals("1min", $this->station->timeToWalk());
        $this->station->setDistance(0.05);
        self::assertEquals("< 1min", $this->station->timeToWalk());

    }

    protected function tearDown(){
        unset($this->station);
    }

}