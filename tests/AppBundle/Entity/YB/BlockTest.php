<?php

namespace Tests\AppBundle\Entity\YB;

use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\BlockRow;

class BlockTest extends \PHPUnit_Framework_TestCase {

    /** @var Block */
    private $block;

    protected function setUp(){
        $this->block = new Block();
        $this->block->setNbRows(20);
        $this->block->setNbSeatsPerRow(30); // salle de 600 places
        $this->block->setRowLabel(1); // row letter : A -> Z
        $this->block->setSeatLabel(2); // row number : 1 -> 30
    }

    public function testGenerateRows(){
        $this->block->generateRows();

        $this->assertEquals(20, count($this->block->getRows()));

        $row = $this->block->getRows()[0];
        $this->assertEquals(30, count($row->getSeats()));
        $this->assertEquals("A", $row->getName());
        $seat = $row->getSeats()[0];
        $this->assertEquals("1", $seat->getName());

        $row2 = $this->block->getRows()[4];
        $this->assertEquals(30, count($row2->getSeats()));
        $this->assertEquals("E", $row2->getName());
        $seat2 = $row2->getSeats()[21];
        $this->assertEquals("22", $seat2->getName());
    }

    public function testRefreshRows(){
        $this->block->generateRows();
        $this->block->refreshRows();
        $this->assertEquals(0, count($this->block->getRows()));
    }

    public function testIsValidCustomRow(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $block->addRow($row2);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);

        $block->setCapacity(85);
        $this->assertTrue($block->isValidCustomRow());

        $block->setCapacity(100);
        $this->assertFalse($block->isValidCustomRow());
    }

    public function testGetNbSeatsOfBlock1(){
        $this->block->setType(Block::UP);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getNbSeatsOfBlock());
    }

    public function testGetNbSeatsOfBlock2(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(true);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getNbSeatsOfBlock());
    }

    public function testGetNbSeatsOfBlock3(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(false);
        $this->block->setNotSquared(false);
        $this->block->setCapacity(700);
        $this->assertEquals(600, $this->block->getNbSeatsOfBlock());
    }

    public function testGetNbSeatsOfBlock4(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(false);
        $this->block->setNotSquared(true);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getNbSeatsOfBlock());
    }

    public function testGetComputedCapacity1(){
        $this->block->setType(Block::UP);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getComputedCapacity());
    }

    public function testGetComputedCapacity2(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(true);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getComputedCapacity());
    }

    public function testGetComputedCapacity3(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(false);
        $this->block->setNotSquared(false);
        $this->block->setCapacity(700);
        $this->assertEquals(600, $this->block->getComputedCapacity());
    }

    public function testGetComputedCapacity4(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $block->addRow($row2);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);
        $block->setType(Block::SEATED);
        $block->setFreeSeating(false);
        $block->setNotSquared(true);
        $block->setCapacity(700);
        $this->assertEquals(85, $block->getComputedCapacity());
    }

    public function testGetSeatedCapacity1(){
        $this->block->setType(Block::UP);
        $this->block->setCapacity(700);
        $this->assertEquals(0, $this->block->getSeatedCapacity());
    }

    public function testGetSeatedCapacity2(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(true);
        $this->block->setCapacity(700);
        $this->assertEquals(700, $this->block->getSeatedCapacity());
    }

    public function testGetSeatedCapacity3(){
        $this->block->setType(Block::SEATED);
        $this->block->setFreeSeating(false);
        $this->block->setNotSquared(false);
        $this->block->setCapacity(700);
        $this->assertEquals(600, $this->block->getSeatedCapacity());
    }

    public function testGetSeatedCapacity4(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $block->addRow($row2);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);
        $block->setType(Block::SEATED);
        $block->setFreeSeating(false);
        $block->setNotSquared(true);
        $block->setCapacity(700);
        $this->assertEquals(85, $block->getSeatedCapacity());
    }

    public function testGenerateSeatChart(){
        $this->block->generateRows();
        $seatChart = $this->block->generateSeatChart();
        $this->assertEquals('ffffffffffffffffffffffffffffff', $seatChart[0]);
    }

    public function testGenerateSeatChartRow(){
        $this->block->generateRows();
        $rowName = $this->block->getSeatChartRow();
        $this->assertEquals('D', $rowName[3]);
        $this->assertEquals('I', $rowName[8]);
        $this->assertEquals('P', $rowName[15]);
    }

    public function testGetSeatAt(){
        $this->block->generateRows();

        $seat = $this->block->getSeatAt(5, 6);
        $this->assertEquals('E', $seat->getRow()->getName());
        $this->assertEquals('6', $seat->getName());

        $seat2 = $this->block->getSeatAt(13, 29);
        $this->assertEquals('M', $seat2->getRow()->getName());
        $this->assertEquals('29', $seat2->getName());
    }

    public function testRemoveSeats(){
        $this->block->generateRows();
        $this->block->removeSeats();
        $this->assertEquals(0, count($this->block->getRows()[0]->getSeats()));
    }

    public function testRetrieveSeats(){
        $this->block->generateRows();
        $seats = $this->block->retrieveSeats();
        $this->assertEquals(600, count($seats));
    }

    protected function tearDown(){
        unset($this->block);
    }

}