<?php

namespace AppBundle\Entity\YB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class Block
 * @package AppBundle\Entity\YB
 * @ORM\Table(name="yb_blocks")
 * @ORM\Entity
 */
class Block {

    public function __construct(){
        $this->rows = new ArrayCollection();
    }

    public function __toString(){
        return $this->name;
    }

    public function generateRows(){
        $alphabet = $this->generateAlphabet();
        if (count($this->rows) > 0){
            foreach ($this->rows as $row){
                $this->removeRow($row);
            }
        }
        for ($i = 0; $i < $this->nbRows; $i++){
            $row = new BlockRow();
            if ($this->rowLabel === 2){
                $name = ''.$i+1;
                $row->setName($name);
            } else {
                $row->setName($alphabet[$i]);
            }
            $row->setBlock($this);
            $row->setNbSeats($this->getNbSeatsPerRow());
            $row->setNumerotationSystem($this->getSeatLabel());
            $this->addRow($row);
        }
        $this->generateSeats();
    }

    public function generateSeats(){
        foreach ($this->rows as $row){
            $row->generateSeats();
        }
    }

    private function generateAlphabet(){
        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }
        return $letters;
    }

    public function constructAllUp(){
        $this->freeSeating = true;
        $this->notSquared = false;
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    public function constructFreeSeating(){
        $this->notSquared = false;
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    public function constructNotSquare(){
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    public function isValidCustomRow(){
        return $this->capacity === $this->getNbSeatsCustomRow();
    }

    public function getNbSeatsOfBlock(){
        if ($this->type === 'Debout'){
            return $this->capacity;
        }
        if ($this->getFreeSeating()){
            return $this->capacity;
        }
        if (!$this->isNotSquared()){
            return $this->nbRows * $this->nbSeatsPerRow;
        } else {
            return $this->capacity;
        }
    }

    private function getNbSeatsCustomRow(){
        $nb = 0;
        foreach ($this->rows as $row){
            $nb += $row->getNbSeats();
        }
        return $nb;
    }

    public function generateSeatChart(){
        $seatChart = array();
        foreach ($this->rows as $row){
            $seatChart[] = $row->generateSeatCharRow();
        }
        return $seatChart;
    }

    public function getSeatChartRow(){
        $seatChartRow = array();
        foreach ($this->rows as $row) {
            $seatChartRow[] = $row->getName();
        }
        return $seatChartRow;
    }

    public function getSeatAt($rowNb, $seatNb){
        $rowIndex = $rowNb - 1;
        /** @var BlockRow $row */ $row = $this->rows[$rowIndex];
        $seatIndex = $seatNb - 1;
        $seat = $row->getSeats()[$seatIndex];
        return $seat;
    }

    public function getMaxSeatsOnRow(){
        if (!$this->isNotSquared()){
            return $this->nbSeatsPerRow;
        } else {
            $max = 0;
            foreach ($this->rows as $row) {
                if ($row->getNbSeats() > $max) {
                    $max = $row->getNbSeats();
                }
            }
            return $max;
        }
    }

    public function isNotNumbered(){
        return $this->type === 'Debout' || $this->getFreeSeating();
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
     * @ORM\Column(name="name", type="string", length=20)
     */
    private $name;

    /**
     * @var
     * @ORM\Column(name="type", type="string", length=15)
     */
    private $type;

    /**
     * @var
     * @ORM\Column(name="capacity", type="integer")
     */
    private $capacity;

    /**
     * @var
     * @ORM\Column(name="free_seating", type="boolean")
     */
    private $freeSeating;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\BlockRow", mappedBy="block", cascade={"all"})
     */
    private $rows;

    /**
     * @var VenueConfig
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\VenueConfig", inversedBy="blocks")
     */
    private $config;

    /**
     * @var boolean
     * @ORM\Column(name="is_not_squared", type="boolean")
     */
    private $notSquared;

    /**
     * @var
     * @ORM\Column(name="nb_rows", type="integer")
     */
    private $nbRows;

    /**
     * @var
     * @ORM\Column(name="nb_seats_per_row", type="integer")
     */
    private $nbSeatsPerRow;
    private $rowLabel;
    private $seatLabel;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CounterPart", mappedBy="venue_blocks")
     */
    private $counterparts;

    /**
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="seat", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $reservations;

    private $bookedSeatList;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param mixed $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * @return mixed
     */
    public function getFreeSeating()
    {
        return $this->freeSeating;
    }

    /**
     * @param mixed $freeSeating
     */
    public function setFreeSeating($freeSeating)
    {
        $this->freeSeating = $freeSeating;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function addRow(BlockRow $row){
        $row->setBlock($this);
        $this->rows->add($row);
    }

    public function removeRow(BlockRow $row){
        $row->setBlock(null);
        $this->rows->removeElement($row);
    }

    /**
     * @return VenueConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param VenueConfig $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
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
     * @return mixed
     */
    public function isNotSquared()
    {
        return $this->notSquared;
    }

    /**
     * @param mixed $notSquared
     */
    public function setNotSquared($notSquared)
    {
        $this->notSquared = $notSquared;
    }

    /**
     * @return mixed
     */
    public function getNbRows()
    {
        return $this->nbRows;
    }

    /**
     * @param mixed $nbRows
     */
    public function setNbRows($nbRows)
    {
        $this->nbRows = $nbRows;
    }

    /**
     * @return mixed
     */
    public function getNbSeatsPerRow()
    {
        return $this->nbSeatsPerRow;
    }

    /**
     * @param mixed $nbSeatsPerRow
     */
    public function setNbSeatsPerRow($nbSeatsPerRow)
    {
        $this->nbSeatsPerRow = $nbSeatsPerRow;
    }

    /**
     * @return mixed
     */
    public function getRowLabel()
    {
        return $this->rowLabel;
    }

    /**
     * @param mixed $rowLabel
     */
    public function setRowLabel($rowLabel)
    {
        $this->rowLabel = $rowLabel;
    }

    /**
     * @return mixed
     */
    public function getSeatLabel()
    {
        return $this->seatLabel;
    }

    /**
     * @param mixed $seatLabel
     */
    public function setSeatLabel($seatLabel)
    {
        $this->seatLabel = $seatLabel;
    }

    /**
     * @return mixed
     */
    public function getCounterparts()
    {
        return $this->counterparts;
    }

    /**
     * @param mixed $counterparts
     */
    public function setCounterparts($counterparts)
    {
        $this->counterparts = $counterparts;
    }

    /**
     * @return mixed
     */
    public function getBookedSeatList()
    {
        return $this->bookedSeatList;
    }

    /**
     * @param mixed $bookedSeatList
     */
    public function setBookedSeatList($bookedSeatList)
    {
        $this->bookedSeatList = $bookedSeatList;
    }



}