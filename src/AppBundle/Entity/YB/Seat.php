<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\CounterPart;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="yb_seats")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\SeatRepository")
 */
class Seat {

    public function __construct($name, $row){
        $this->name = $name;
        $this->row = $row;
    }

    public function __toString(){
        return 'bloc : '.$this->getBlock().' - rangée : '.$this->row->getName().' - siège : '.$this->name;
    }

    /**
     * For the plugin 'JQuery Seat Chart' :
     * Get a string representing the seat
     * The seat no 5 at the row 6 will give a string like '6_5'
     * @return string
     */
    public function getSeatChartName(){
        if ($this->row->getNumerotationSystem() === 2){
            return $this->row->getRowNumber() . '_' . $this->name;
        } else {
            return $this->row->getRowNumber() . '_' . $this->getSeatNumber();
        }

    }

    /**
     * Gives the index of the seat in its row
     * @return int
     */
    private function getSeatNumber(){
        for ($i=0; $i<count($this->row->getSeats()); $i++){
            if ($this->row->getSeats()[$i] === $this){
                return $i + 1;
            }
        }
    }

    /**
     * Retrieve the block to whom the seat belongs
     * @return Block
     */
    private function getBlock(){
        return $this->row->getBlock();
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