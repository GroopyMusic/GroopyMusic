<?php

namespace AppBundle\Entity\YB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\Photo;
use Vich\UploaderBundle\Entity\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Venue
 * @ORM\table(name="yb_venues_config")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\VenueConfigRepository")
 * @Vich\Uploadable
 */
class VenueConfig {

    const PHOTOS_DIR = 'images/venues/';
    const PHOTOS_DIR_YB = 'yb/images/venues/';

    public static function getWebPath($image) {
        return self::PHOTOS_DIR . $image;
    }

    public static function getYBWebPath($image){
        return self::PHOTOS_DIR_YB . $image;
    }

    public function __construct(){
        $this->isDefault = false;
        $this->maxCapacity = 0;
        $this->nbStandUp = 0;
        $this->nbSeatedSeats = 0;
        $this->nbBalconySeats = 0;
        $this->blocks = new ArrayCollection();
        $this->photo = null;
        $this->image = '';
    }

    public function __toString(){
        return $this->name;
    }

    /**
     * Creates a default configuration
     * @param Venue $v
     */
    public function constructDefault(Venue $v){
        $this->name = 'Config par défaut';
        $this->maxCapacity = $v->getDefaultCapacity();
        $this->onlyStandup = true;
        $this->nbStandUp = $this->maxCapacity;
        $this->nbSeatedSeats = 0;
        $this->nbBalconySeats = 0;
        $this->pmrAccessible = false;
        $this->emailAddressPMR = null;
        $this->phoneNumberPMR = null;
        $this->hasFreeSeatingPolicy = true;
        $this->venue = $v;
        $this->photo = null;
        $this->isDefault = true;
        $this->image = '';
    }

    /**
     * Generates all the row for the configurations
     */
    public function generateRows(){
        foreach ($this->blocks as $block){
            if ($block->isNotSquared()){
                // do nothing
            } else {
                $block->generateRows();
            }
        }
    }

    /**
     * Checks if the configuration has some blocks that are not squared
     * @return bool
     */
    public function hasUnsquaredBlock(){
        foreach ($this->blocks as $block){
            if ($block->isNotSquared()){
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves all the blocks that are not squared
     * @return array
     */
    public function getUnsquaredBlocks(){
        $unsquaredBlocks = array();
        foreach ($this->blocks as $block){
            if ($block->isNotSquared()){
                array_push($unsquaredBlocks, $block);
            }
        }
        return $unsquaredBlocks;
    }

    /**
     * Generates all the seats for unsquared blocks
     */
    public function generateSeatForUnsquareRows(){
        $unsquaredBlks = $this->getUnsquaredBlocks();
        /** @var Block $blk */
        foreach ($unsquaredBlks as $blk){
            $blk->removeSeats();
            $blk->generateSeats();
        }
    }

    /**
     * Get the total capacity of the configuration
     * @return int|mixed
     */
    public function getTotalCapacity(){
        if ($this->venue->isOnlyFreeSeating() || $this->isDefault()){
            return $this->venue->getDefaultCapacity();
        }
        if ($this->isOnlyStandup() || $this->hasFreeSeatingPolicy()){
            return $this->getMaxCapacity();
        }
        $capacity = 0;
        foreach ($this->blocks as $block){
            $capacity += $block->getComputedCapacity();
        }
        return $capacity;
    }

    /**
     * Checks if the configuration has only blocks where the people have to stand/sit at a specific seat.
     * @return bool
     */
    public function hasOnlySeatedBlocks(){
        /** @var Block $block */
        foreach ($this->getBlocks() as $block){
            if ($block->getType() === Block::UP){
                return false;
            }
        }
        return true;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var
     * @ORM\Column(name="max_capacity", type="integer")
     */
    private $maxCapacity;

    /**
     * @var
     * @ORM\Column(name="is_only_standup", type="boolean")
     */
    private $onlyStandup;

    /**
     * @var
     * @ORM\Column(name="nb_standup", type="integer")
     */
    private $nbStandUp;

    /**
     * @var
     * @ORM\Column(name="nb_seated_seats", type="integer")
     */
    private $nbSeatedSeats;

    /**
     * @var
     * @ORM\Column(name="nb_balcony_seats", type="integer")
     */
    private $nbBalconySeats;

    /**
     * @var
     * @ORM\Column(name="is_pmr_accessible", type="boolean")
     */
    private $pmrAccessible;

    /**
     * @var
     * @ORM\Column(name="email_PMR", type="string", length=50, nullable=true)
     */
    private $emailAddressPMR;

    /**
     * @var
     * @ORM\Column(name="phone_PMR", type="string", length=15, nullable=true)
     */
    private $phoneNumberPMR;

    /**
     * @var
     * @ORM\Column(name="hasFreeSeatingPolicy", type="boolean")
     */
    private $hasFreeSeatingPolicy;

    /**
     * @var Venue
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Venue", inversedBy="configurations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $venue;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Block", mappedBy="config", cascade={"all"})
     */
    private $blocks;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBContractArtist", mappedBy="config", cascade={"all"})
     */
    private $events;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Photo", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $photo;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $isDefault;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="yb_venue_header", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

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
    public function getMaxCapacity()
    {
        return $this->maxCapacity;
    }

    /**
     * @param mixed $maxCapacity
     */
    public function setMaxCapacity($maxCapacity)
    {
        $this->maxCapacity = $maxCapacity;
    }

    /**
     * @return mixed
     */
    public function isOnlyStandup()
    {
        return $this->onlyStandup;
    }

    /**
     * @param mixed $onlyStandup
     */
    public function setOnlyStandup($onlyStandup)
    {
        $this->onlyStandup = $onlyStandup;
    }

    /**
     * @return mixed
     */
    public function getNbStandUp()
    {
        return $this->nbStandUp;
    }

    /**
     * @param mixed $nbStandUp
     */
    public function setNbStandUp($nbStandUp)
    {
        $this->nbStandUp = $nbStandUp;
    }

    /**
     * @return mixed
     */
    public function getNbSeatedSeats()
    {
        return $this->nbSeatedSeats;
    }

    /**
     * @param mixed $nbSeatedSeats
     */
    public function setNbSeatedSeats($nbSeatedSeats)
    {
        $this->nbSeatedSeats = $nbSeatedSeats;
    }

    /**
     * @return mixed
     */
    public function getNbBalconySeats()
    {
        return $this->nbBalconySeats;
    }

    /**
     * @param mixed $nbBalconySeats
     */
    public function setNbBalconySeats($nbBalconySeats)
    {
        $this->nbBalconySeats = $nbBalconySeats;
    }

    /**
     * @return mixed
     */
    public function isPmrAccessible()
    {
        return $this->pmrAccessible;
    }

    /**
     * @param mixed $pmrAccessible
     */
    public function setPmrAccessible($pmrAccessible)
    {
        $this->pmrAccessible = $pmrAccessible;
    }

    /**
     * @return mixed
     */
    public function getEmailAddressPMR()
    {
        return $this->emailAddressPMR;
    }

    /**
     * @param mixed $emailAddressPMR
     */
    public function setEmailAddressPMR($emailAddressPMR)
    {
        $this->emailAddressPMR = $emailAddressPMR;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumberPMR()
    {
        return $this->phoneNumberPMR;
    }

    /**
     * @param mixed $phoneNumberPMR
     */
    public function setPhoneNumberPMR($phoneNumberPMR)
    {
        $this->phoneNumberPMR = $phoneNumberPMR;
    }

    /**
     * @return Venue
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @param Venue $venue
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;
    }

    /**
     * @return mixed
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    public function addBlock(Block $block){
        $block->setConfig($this);
        $this->blocks->add($block);
    }

    public function removeBlock(Block $block){
        $block->setConfig(null);
        $this->blocks->removeElement($block);
    }

    /**
     * @return mixed
     */
    public function hasFreeSeatingPolicy()
    {
        return $this->hasFreeSeatingPolicy;
    }

    /**
     * @param mixed $hasFreeSeatingPolicy
     */
    public function setHasFreeSeatingPolicy($hasFreeSeatingPolicy)
    {
        $this->hasFreeSeatingPolicy = $hasFreeSeatingPolicy;
    }

    /**
     * @return YBContractArtist
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param YBContractArtist $events
     */
    public function setEvents($events){
        $this->events = $events;
    }

    public function getDisplayName(){
        return $this->venue->getAddress()->getName() . ' ('.$this->name.')';
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param mixed $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param File $imageFile
     */
    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
        if ($imageFile){
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }





}