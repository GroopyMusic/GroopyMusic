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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CounterPart", inversedBy="reservations")
     * @ORM\JoinColumn(name="counterpart_id", referencedColumnName="id", nullable=FALSE)
     */
    private $counterpart;

    /**
     * @ORM\Column(type="boolean", name="isBooked")
     */
    protected $isBooked;

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
    public function isBooked()
    {
        return $this->isBooked;
    }

    /**
     * @param boolean $isBooked
     */
    public function setIsBooked($isBooked)
    {
        $this->isBooked = $isBooked;
    }

    /**
     * @return mixed
     */
    public function getCounterpart()
    {
        return $this->counterpart;
    }

    /**
     * @param mixed $counterpart
     */
    public function setCounterpart($counterpart)
    {
        $this->counterpart = $counterpart;
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


}