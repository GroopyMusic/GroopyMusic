<?php
/**
 * Created by PhpStorm.
 * User: s_u_y_s_a
 * Date: 2019-05-22
 * Time: 16:38
 */

namespace Tests\AppBundle\Entity;


use AppBundle\Entity\CounterPart;
use AppBundle\Entity\YB\Block;

class CounterPartTest extends \PHPUnit_Framework_TestCase
{

    /** @var CounterPart */
    private $cp;

    protected function setUp()
    {
        $this->cp = new CounterPart();
    }

    public function testCanOverPassVenueCapacity1(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk2 = new Block();
        $blk2->setType(Block::UP);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertTrue($this->cp->canOverpassVenueCapacity());
    }

    public function testCanOverPassVenueCapacity2(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk2 = new Block();
        $blk2->setType(Block::UP);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertTrue($this->cp->canOverpassVenueCapacity());
    }

    public function testCanOverPassVenueCapacity3(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertFalse($this->cp->canOverpassVenueCapacity());
    }

    public function testIsCapacityMaxReach(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk1->setNbRows(10);
        $blk1->setNbSeatsPerRow(20);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        $this->cp->setAccessEverywhere(false);
        $this->cp->setMaximumAmount(400);
        self::assertTrue($this->cp->isCapacityMaxReach());
        $this->cp->setMaximumAmount(300);
        self::assertFalse($this->cp->isCapacityMaxReach());
    }

    public function testHasOnlySeatedBlock1(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertTrue($this->cp->hasOnlySeatedBlock(array($blk1, $blk2)));
    }

    public function testHasOnlySeatedBlock2(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertFalse($this->cp->hasOnlySeatedBlock(array($blk1, $blk2)));
    }

    public function testHasOnlyFreeSeatingBlocks1(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertFalse($this->cp->hasOnlyFreeSeatingBlocks(array($blk1, $blk2)));
    }

    public function testHasOnlyFreeSeatingBlocks2(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk2 = new Block();
        $blk2->setType(Block::UP);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertTrue($this->cp->hasOnlyFreeSeatingBlocks(array($blk1, $blk2)));
    }

    public function testGetSeatedCapacity1(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk1->setNbRows(10);
        $blk1->setNbSeatsPerRow(20);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertEquals(350, $this->cp->getSeatedCapacity(array($blk1, $blk2)));
    }

    public function testGetSeatedCapacity2(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk1->setNbRows(10);
        $blk1->setNbSeatsPerRow(20);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertEquals(150, $this->cp->getSeatedCapacity(array($blk1, $blk2)));
    }

    public function testGetStandUpCapacity1(){
        $blk1 = new Block();
        $blk1->setType(Block::BALCONY);
        $blk1->setNbRows(10);
        $blk1->setNbSeatsPerRow(20);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertEquals(0, $this->cp->getStandUpCapacity(array($blk1, $blk2)));
    }

    public function testGetStandUpCapacity2(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk1->setCapacity(200);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        self::assertEquals(200, $this->cp->getStandUpCapacity(array($blk1, $blk2)));
    }

    public function testGetDifferenceBetweenPhysicalAndTicketCapacity(){
        $blk1 = new Block();
        $blk1->setType(Block::UP);
        $blk1->setCapacity(200);
        $blk2 = new Block();
        $blk2->setType(Block::SEATED);
        $blk2->setNbRows(15);
        $blk2->setNbSeatsPerRow(10);
        $this->cp->setVenueBlocks(array($blk1, $blk2));
        $this->cp->setMaximumAmount(500);
        self::assertEquals(150, $this->cp->getDifferenceBetweenPhysicalAndTicketCapacity(array($blk1, $blk2)));
    }

    protected function tearDown()
    {
        unset($this->cp);
    }

}