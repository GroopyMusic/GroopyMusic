<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class Reservation
 * @package AppBundle\Entity\YB
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\ReservationRepository")
 * @ORM\Table(name="yb_reservations",
    uniqueConstraints={
        @ORM\UniqueConstraint(name="reservation_unique", columns={"seat_id", "counterpart_id"})
    })
 */
class Reservation {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Seat", inversedBy="reservations")
     * @ORM\JoinColumn(name="seat_id", referencedColumnName="id", nullable=FALSE)
     */
    private $seat;

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
    public function getSeat()
    {
        return $this->seat;
    }

    /**
     * @param mixed $seat
     */
    public function setSeat($seat)
    {
        $this->seat = $seat;
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


}