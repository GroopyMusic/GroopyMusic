<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Purchase;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\YB\EnumRole;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\BookingRepository")
 * @ORM\Table(name="yb_purchase_reservations",
uniqueConstraints={
@ORM\UniqueConstraint(name="purchase_reservations_unique", columns={"purchase_id", "reservation_id"})
})
 */
class Booking {

    public function __construct($reservation, $purchase){
        $this->reservation = $reservation;
        $this->purchase = $purchase;
        $this->bookingDate = new \DateTime();
        $this->isBooked = false;
    }

    public function __toString(){
        return $this->reservation . ' ' . $this->purchase;
    }

    /**
     * Check if the booking is not related to a numbered seat
     * @return bool
     */
    public function hasNoNumberedSeat(){
        return $this->reservation->getRowIndex() === -1 && $this->reservation->getSeatIndex() === -1;
    }

    /**
     * Get the block of the booking
     * @return Block
     */
    public function getBlock(){
        return $this->reservation->getBlock();
    }

    /**
     * Returns a string representing the seat concerned by the booking
     * @return string
     */
    public function getSeat(){
        $blk = $this->reservation->getBlock();
        if ($this->hasNoNumberedSeat()){
            return 'Placement libre dans le bloc : '.$blk;
        } else {
            $seat = $blk->getSeatAt($this->reservation->getRowIndex(), $this->reservation->getSeatIndex());
        }
        return $seat->__toString();
    }

    public function getRuntimeMax(){
        $time = $this->bookingDate;
        $runTimeMax = new \DateTime($time->format('Y-m-d H:i:s'));
        $runTimeMax = $runTimeMax->modify('+15 minutes');
        $timeStamp = $runTimeMax->getTimestamp();
        return $timeStamp;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="booking_date", type="datetime", nullable=true)
     */
    private $bookingDate;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Purchase", inversedBy="bookings")
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="Reservation", inversedBy="bookings", cascade={"persist"})
     * @ORM\JoinColumn(name="reservation_id", referencedColumnName="id", nullable=FALSE)
     */
    protected $reservation;

    /**
     * @ORM\Column(type="boolean", name="isBooked")
     */
    protected $isBooked;

    // getters and setters

    public function getId(){
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getBookingDate()
    {
        return $this->bookingDate;
    }

    /**
     * @param \DateTime $bookingDate
     */
    public function setBookingDate($bookingDate)
    {
        $this->bookingDate = $bookingDate;
    }

    /**
     * @return mixed
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * @param Purchase $purchase
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * @return mixed
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param Reservation $reservation
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * @return mixed
     */
    public function getisBooked()
    {
        return $this->isBooked;
    }

    /**
     * @param mixed $isBooked
     */
    public function setIsBooked($isBooked)
    {
        $this->isBooked = $isBooked;
    }



}