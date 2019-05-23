<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class Reservation
 * @package AppBundle\Entity\YB
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\ReservationRepository")
 * @ORM\Table(name="yb_reservations")
 */
class Reservation {

    public function __construct($block, $rowIndex, $seatIndex){
        $this->block = $block;
        $this->rowIndex = $rowIndex;
        $this->seatIndex = $seatIndex;
    }

    public function __toString(){
        return $this->block->getName() . ' - ' . $this->rowIndex . ' - ' .$this->seatIndex;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="reservations")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id", nullable=FALSE)
     */
    private $block;

    /**
     * @ORM\Column(type="integer", name="row_index")
     */
    private $rowIndex;

    /**
     * @ORM\Column(type="integer", name="seat_index")
     */
    private $seatIndex;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Booking", mappedBy="reservation", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $bookings;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param mixed $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return mixed
     */
    public function getRowIndex()
    {
        return $this->rowIndex;
    }

    /**
     * @param mixed $rowIndex
     */
    public function setRowIndex($rowIndex)
    {
        $this->rowIndex = $rowIndex;
    }

    /**
     * @return mixed
     */
    public function getSeatIndex()
    {
        return $this->seatIndex;
    }

    /**
     * @param mixed $seatIndex
     */
    public function setSeatIndex($seatIndex)
    {
        $this->seatIndex = $seatIndex;
    }

    /**
     * @return mixed
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param mixed $bookings
     */
    public function setBookings($bookings)
    {
        $this->bookings = $bookings;
    }

}