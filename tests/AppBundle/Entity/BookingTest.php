<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\Booking;
use AppBundle\Entity\YB\Reservation;
use AppBundle\Entity\YB\YBContractArtist;
use Doctrine\Common\Collections\ArrayCollection;

class BookingTest extends \PHPUnit_Framework_TestCase {

    /** @var Booking $booking */
    private $booking;

    protected function setUp(){
        $ca = $this->createMock(YBContractArtist::class);
        $ca->method('getCounterParts')->willReturn(new ArrayCollection());
        $cf = new ContractFan($ca);
        $purchase = new Purchase();
        $purchase->setContractFan($cf);
        $blk = $this->constructBlk();
        $rsv = new Reservation($blk, 5, 9);
        $this->booking = new Booking($rsv, $purchase);
    }

    public function testGetSeat1(){
        $this->booking->getReservation()->getBlock()->generateRows();
        $seat = $this->booking->getSeat();
        self::assertEquals('bloc : Est - rangée : E - siège : 9', $seat);
    }

    public function testGetSeat2(){
        $this->booking->getReservation()->setRowIndex(-1);
        $this->booking->getReservation()->setSeatIndex(-1);
        $this->booking->getReservation()->getBlock()->generateRows();
        $seat = $this->booking->getSeat();
        self::assertEquals('Placement libre dans le bloc : Est', $seat);
    }

    public function testGetRuntimeMax(){
        $date = new \DateTime('2019-05-21 12:40:00');
        $this->booking->setBookingDate($date);
        $timestamp = $this->booking->getRuntimeMax();
        self::assertEquals(1558443300, $timestamp);
    }

    private function constructBlk(){
        $blk = new Block();
        $blk->setName("Est");
        $blk->setNbRows(20);
        $blk->setNbSeatsPerRow(30);
        $blk->setRowLabel(1); // letter
        $blk->setSeatLabel(2); // number
        return $blk;
    }

    protected function tearDown(){
        unset($this->booking);
    }

}