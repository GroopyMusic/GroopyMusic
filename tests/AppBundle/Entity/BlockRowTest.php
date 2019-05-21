<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\BlockRow;

class BlockRowTest extends \PHPUnit_Framework_TestCase {

    /** @var BlockRow */
    private $row;

    protected function setUp(){
        $this->row = new BlockRow();
        $this->row->setNbSeats(45);
        $this->row->setName('7');
    }

    public function testGenerateSeats1(){
        $this->row->setNumerotationSystem(1);
        $this->row->generateSeats();
        $this->assertEquals(45, count($this->row->getSeats()));
        $seat = $this->row->getSeats()[4];
        $this->assertEquals('E', $seat->getName());
    }

    public function testGenerateSeats2(){
        $this->row->setNumerotationSystem(2);
        $this->row->generateSeats();
        $this->assertEquals(45, count($this->row->getSeats()));
        $seat = $this->row->getSeats()[4];
        $this->assertEquals('5', $seat->getName());
    }

    public function testRemoveSeats(){
        $this->row->generateSeats();
        $this->row->removeSeats();
        $this->assertEquals(0, count($this->row->getSeats()));
    }

    public function testGenerateSeatCharRow(){
        $this->row->setNbSeats(5);
        $this->assertEquals('fffff', $this->row->generateSeatCharRow());
        $this->row->setNbSeats(12);
        $this->assertEquals('ffffffffffff', $this->row->generateSeatCharRow());
    }

    public function testGetRowNumber(){
        $block = new Block();
        $row1 = new BlockRow();
        $row1->setNbSeats(30);
        $block->addRow($row1);
        $row2 = new BlockRow();
        $row2->setNbSeats(15);
        $block->addRow($row2);
        $block->addRow($this->row);
        $this->row->setBlock($block);
        $row3 = new BlockRow();
        $row3->setNbSeats(10);
        $block->addRow($row3);
        $row4 = new BlockRow();
        $row4->setNbSeats(30);
        $block->addRow($row4);
        $this->assertEquals(3, $this->row->getRowNumber());
    }

    protected function tearDown(){
        unset($this->row);
    }

}