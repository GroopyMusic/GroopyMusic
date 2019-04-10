<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="yb_seats")
 * @ORM\Entity
 */
class Seat {

    public function __construct($name, $row){
        $this->name = $name;
        $this->row = $row;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     * @ORM\Column(name="name", type="string", length=5)
     */
    private $name;

    /**
     * @var BlockRow
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\BlockRow", inversedBy="seats")
     */
    private $row;

    /**
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="seat", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $reservations;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Block
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param Block $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }

    /**
     * @return mixed
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * @param mixed $reservations
     */
    public function setReservations($reservations)
    {
        $this->reservations = $reservations;
    }








}