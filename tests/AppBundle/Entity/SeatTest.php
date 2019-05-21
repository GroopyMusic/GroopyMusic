<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\BlockRow;
use AppBundle\Entity\YB\Seat;

class SeatTest extends \PHPUnit_Framework_TestCase {


    protected function setUp(){
        parent::setUp();
    }

    public function testGetSeatChartName1(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $row2->setNumerotationSystem(2);
        $block->addRow($row2);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);
        $seat = new Seat("16", $row2);
        self::assertEquals("2_16", $seat->getSeatChartName());
    }

    public function testGetSeatChartName2(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $row2->setNumerotationSystem(1);
        $seat1 = new Seat("A", $row2);
        $seat2 = new Seat("B", $row2);
        $seat3 = new Seat("C", $row2);
        $row2->addSeat($seat1);
        $row2->addSeat($seat2);
        $row2->addSeat($seat3);
        $block->addRow($row2);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);
        self::assertEquals("2_3", $seat3->getSeatChartName());
    }

    protected function tearDown(){
        parent::tearDown();
    }

}