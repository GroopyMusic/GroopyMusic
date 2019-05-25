<?php

namespace Tests\AppBundle\Entity\YB;

use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\BlockRow;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\VenueConfig;
use Beta\B;

class VenueConfigTest extends \PHPUnit_Framework_TestCase {

    /** @var VenueConfig $config */
    private $config;

    protected function setUp(){
        $this->config = new VenueConfig();

        $blk1 = new Block();
        $blk1->setNbRows(10);
        $blk1->setRowLabel(1);
        $blk1->setNbSeatsPerRow(15);
        $blk1->setSeatLabel(2);
        $this->config->addBlock($blk1);

        $blk2 = new Block();
        $blk2->setNbRows(5);
        $blk2->setRowLabel(1);
        $blk2->setNbSeatsPerRow(20);
        $blk2->setSeatLabel(2);
        $this->config->addBlock($blk2);

        $blk3 = new Block();
        $blk3->setNbRows(10);
        $blk3->setRowLabel(1);
        $blk3->setNbSeatsPerRow(15);
        $blk3->setSeatLabel(2);
        $this->config->addBlock($blk3);

        $blk4 = new Block();
        $blk4->setName("ABC");
        $blk4->setNotSquared(true);
        $this->config->addBlock($blk4);

        $blk5 = new Block();
        $blk5->setName("DEF");
        $blk5->setNotSquared(true);
        $this->config->addBlock($blk5);
    }

    public function testGenerateRows(){
        $this->config->generateRows();
        self::assertEquals(10, count($this->config->getBlocks()[0]->getRows()));
        self::assertEquals(5, count($this->config->getBlocks()[1]->getRows()));
        self::assertEquals(10, count($this->config->getBlocks()[2]->getRows()));
        self::assertEquals(15, count($this->config->getBlocks()[0]->getRows()[0]->getSeats()));
        self::assertEquals(20, count($this->config->getBlocks()[1]->getRows()[0]->getSeats()));
        self::assertEquals(15, count($this->config->getBlocks()[2]->getRows()[0]->getSeats()));
    }

    public function testHasUnsquaredBlock(){
        self::assertTrue($this->config->hasUnsquaredBlock());
    }

    public function testGetUnsquaredBlocks(){
        $unsquared = $this->config->getUnsquaredBlocks();
        self::assertEquals(2, count($unsquared));
        self::assertEquals("ABC", $unsquared[0]->getName());
    }

    public function testGenerateSeatForUnsquareRows(){
        $config = new VenueConfig();
        $blk1 = new Block();
        $blk1->setNotSquared(true);
        $row1 = new BlockRow();
        $row1->setNbSeats(10);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $blk1->addRow($row1);
        $blk1->addRow($row2);
        $config->addBlock($blk1);
        $blk2 = new Block();
        $config->addBlock($blk2);
        $config->generateSeatForUnsquareRows();
        self::assertEquals(10, count($config->getBlocks()[0]->getRows()[0]->getSeats()));
        self::assertEquals(15, count($config->getBlocks()[0]->getRows()[1]->getSeats()));
    }

    public function testGetTotalCapacity1(){
        $venue = $this->createMock(Venue::class);
        $venue->method('isOnlyFreeSeating')->willReturn(true);
        $venue->method('getDefaultCapacity')->willReturn(500);
        $this->config->setVenue($venue);
        self::assertEquals(500, $this->config->getTotalCapacity());
    }

    public function testGetTotalCapacity2(){
        $venue = $this->createMock(Venue::class);
        $venue->method('getDefaultCapacity')->willReturn(500);
        $this->config->setVenue($venue);
        $this->config->setIsDefault(true);
        self::assertEquals(500, $this->config->getTotalCapacity());
    }

    public function testGetTotalCapacity3(){
        $venue = $this->createMock(Venue::class);
        $venue->method('isOnlyFreeSeating')->willReturn(false);
        $this->config->setVenue($venue);
        $this->config->setHasFreeSeatingPolicy(true);
        $this->config->setMaxCapacity(400);
        self::assertEquals(400, $this->config->getTotalCapacity());
    }

    public function testGetTotalCapacity4(){
        $venue = $this->createMock(Venue::class);
        $venue->method('isOnlyFreeSeating')->willReturn(false);
        $this->config->setVenue($venue);
        $this->config->setOnlyStandup(true);
        $this->config->setMaxCapacity(400);
        self::assertEquals(400, $this->config->getTotalCapacity());
    }

    public function testGetTotalCapacity5(){
        $venue = $this->createMock(Venue::class);
        $venue->method('isOnlyFreeSeating')->willReturn(false);
        $this->config->setVenue($venue);
        $this->config->setMaxCapacity(300);
        self::assertEquals(400, $this->config->getTotalCapacity());
    }

    public function testHasOnlySeatedBlocks1(){
        self::assertTrue($this->config->hasOnlySeatedBlocks());
    }

    public function testHasOnlySeatedBlocks2(){
        $blk6 = new Block();
        $blk6->setType(Block::UP);
        $this->config->addBlock($blk6);
        self::assertFalse($this->config->hasOnlySeatedBlocks());
    }

    protected function tearDown(){
        unset($this->config);
    }

}