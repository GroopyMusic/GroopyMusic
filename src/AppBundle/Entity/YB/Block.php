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

    const UP = 'Debout';
    const SEATED = 'Assis';
    const BALCONY = 'Balcon';

    public function __construct(){
        $this->rows = new ArrayCollection();
    }

    public function __toString(){
        return $this->name;
    }

    /**
     * Remove all the row from the block
     */
    public function refreshRows(){
        if (count($this->rows) > 0){
            foreach ($this->rows as $row){
                $this->removeRow($row);
            }
        }
    }

    /**
     * Generates all the rows of a block
     * And for each row, we generate the seats as well
     */
    public function generateRows(){
        $alphabet = $this->generateAlphabet();
        $this->refreshRows();
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

    /**
     * Generates for each rows, the seats
     */
    public function generateSeats(){
        foreach ($this->rows as $row){
            $row->generateSeats();
        }
    }

    /**
     * Generates the alphabet
     * Once we have reached Z, we start over at AA, AB, AC,... until ZZ.
     * @return array
     */
    private function generateAlphabet(){
        $letters = array();
        $letter = 'A';
        while ($letter !== 'AAA') {
            $letters[] = $letter++;
        }
        return $letters;
    }

    /**
     * Creates the block if the block is of type "UP"
     */
    public function constructAllUp(){
        $this->freeSeating = true;
        $this->notSquared = false;
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    /**
     * Creates the block if the block has a free seating policy
     * The participants can seat wherever they want
     */
    public function constructFreeSeating(){
        $this->notSquared = false;
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    /**
     * Creates a block if the block is unsquared (has uneven rows)
     */
    public function constructNotSquare(){
        $this->nbRows = 0;
        $this->nbSeatsPerRow = 0;
        $this->rowLabel = 1;
        $this->seatLabel = 1;
    }

    /**
     * Used when the block is not squared : checks if the computed number of seats fits the block capacity
     * @return bool
     */
    public function isValidCustomRow(){
        return $this->capacity === $this->getNbSeatsCustomRow();
    }

    /**
     * Compute the number of seats in the block
     * Unsquared block are not taken into account
     * @return float|int
     */
    public function getNbSeatsOfBlock(){
        if ($this->type === self::UP){
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

    /**
     * Compute the number of seats in the block
     * Unsquared block are taken into account
     * @return float|int
     */
    public function getComputedCapacity(){
        if ($this->type === self::UP){
            return $this->capacity;
        }
        if ($this->getFreeSeating()){
            return $this->capacity;
        }
        if (!$this->isNotSquared()){
            return $this->nbRows * $this->nbSeatsPerRow;
        } else {
            return $this->getNbSeatsCustomRow();
        }
    }

    /**
     * Get the seated capacity of a block
     * @return float|int
     */
    public function getSeatedCapacity(){
        if ($this->type === self::UP){
            return 0;
        } else {
            if ($this->getFreeSeating()){
                return $this->capacity;
            }
            if (!$this->isNotSquared()){
                return $this->nbRows * $this->nbSeatsPerRow;
            } else {
                return $this->getNbSeatsCustomRow();
            }
        }
    }

    /**
     * Computes the number of seats in the case of an unsquared block
     * @return int
     */
    private function getNbSeatsCustomRow(){
        $nb = 0;
        foreach ($this->rows as $row){
            $nb += $row->getNbSeats();
        }
        return $nb;
    }

    /**
     * For the plugin 'JQuery Seat Chart', we have to transform the block in a 2d array of char
     * Here, we creates this array that looks like this :
     * fffffffff
     * fffffffff
     * fffffffff
     * for a block of 3 rows of 9 seats
     * 
     * @return array
     */
    public function generateSeatChart(){
        $seatChart = array();
        foreach ($this->rows as $row){
            $seatChart[] = $row->generateSeatCharRow();
        }
        return $seatChart;
    }

    /**
     * We retrieve the name of the row (either a letter or a number)
     * This name will be displayed and used by the plugin 'JQuery Seat Chart'
     * @return array
     */
    public function getSeatChartRow(){
        $seatChartRow = array();
        foreach ($this->rows as $row) {
            $seatChartRow[] = $row->getName();
        }
        return $seatChartRow;
    }

    /**
     * Get the seat in the given row at the given index
     * @param $rowNb
     * @param $seatNb
     * @return Seat
     */
    public function getSeatAt($rowNb, $seatNb){
        $rowIndex = $rowNb - 1;
        /** @var BlockRow $row */ $row = $this->rows[$rowIndex];
        $seatIndex = $seatNb - 1;
        $seat = $row->getSeats()[$seatIndex];
        return $seat;
    }

    /*public function getMaxSeatsOnRow(){
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
    }*/

    /**
     * Checks if a block is not numbered (if it is not composed of numbered seat)
     * @return bool
     */
    public function isNotNumbered(){
        return $this->type === self::UP || $this->getFreeSeating();
    }

    /**
     * Checks in all the reservations of en event and retrieve all the tickets booked in the block
     * @param YBContractArtist $event
     * @return int
     */
    public function getSoldTicketInBlock(YBContractArtist $event){
        $nb = 0;
        /** @var Reservation $rsv */
        foreach ($this->reservations as $rsv){
            /** @var Booking $booking */
            foreach ($rsv->getBookings() as $booking){
                if ($booking->getPurchase()->getContractFan()->getContractArtist() === $event){
                    $nb++;
                }
            }
        }
        return $nb;
    }

    /**
     * Remove all the seats from the block
     */
    public function removeSeats(){
        /** @var BlockRow $row */
        foreach ($this->rows as $row){
            $row->removeSeats();
        }
    }

    public function retrieveSeats(){
        $seats = [];
        for ($i = 0; $i < count($this->rows); $i++){
            /** @var BlockRow $row */ $row = $this->rows[$i];
            $ss = $row->getSeats();
            foreach ($ss as $s){
                $seats[] = $s;
            }
        }
        return $seats;
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
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="block", cascade={"persist", "remove"}, orphanRemoval=TRUE)
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