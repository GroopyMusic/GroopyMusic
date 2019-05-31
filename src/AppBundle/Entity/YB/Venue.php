<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * Class Venue
 * @ORM\table(name="yb_venues")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\VenueRepository")
 */
class Venue {

    use ORMBehaviors\SoftDeletable\SoftDeletable;

    const OWN_VENUE =  '--- (MA SALLE)';

    public function __construct(){
        $this->configurations = new ArrayCollection();
    }

    public function __toString(){
        return $this->name;
    }

    public function isLocatedInBelgium(){
        return $this->address->getCountry() === 'BE';
    }

    /**
     * Add a configuration to the venue
     * @param VenueConfig $config
     */
    public function addConfiguration(VenueConfig $config){
        $config->setVenue($this);
        $this->configurations->add($config);
    }

    /**
     * Remove the given configuration from the venue
     * @param VenueConfig $config
     */
    public function removeConfiguration(VenueConfig $config){
        $config->setVenue(null);
        $this->configurations->removeElement($config);
    }

    /**
     * Get all the users that are members of the organization in charged of the venue
     * @return array
     */
    public function getHandlers(){
        return $this->organization->getMembers();
    }

    /**
     * Checks if the given user is in charged of the venue
     * @param User $user
     * @return bool
     */
    public function isHandledByUser(User $user){
        return in_array($user, $this->organization->getMembers());
    }

    /**
     * Gets all the rows of the venue
     * @return array
     */
    public function getRows(){
        $rows = [];
        foreach ($this->configurations as $config){
            foreach ($config->getBlocks() as $block){
                foreach ($block->getRows() as $row){
                    array_map($row, $rows);
                }
            }
        }
        return $rows;
    }

    /**
     * Generates the rows for each block that is squared.
     * If the block is not squared, it does nothing
     */
    public function generateRows(){
        foreach ($this->configurations as $config){
            foreach ($config->getBlocks() as $block){
                if ($block->isNotSquared()){
                    // nothing
                } else {
                    $block->generateRows();
                }
            }
        }
    }

    /**
     * Checks if the venue has a free seating policy (people can stand/sit wherever they want)
     * @return bool
     */
    public function isOnlyFreeSeating(){
        foreach ($this->configurations as $configuration){
            if ($configuration->isOnlyStandup()){
                // OK
            } else if ($configuration->hasFreeSeatingPolicy()){
                // OK
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Retrieve all the configurations from the venue except its default configuration
     * @return array
     */
    public function getNotDefaultConfig(){
        $nonDefault = [];
        foreach ($this->configurations as $configuration){
            if (!$configuration->isDefault()){
                array_push($nonDefault, $configuration);
            }
        }
        return $nonDefault;
    }

    /**
     * Generates a default configuration for the venue
     */
    public function createDefaultConfig(){
        $config = new VenueConfig();
        $config->constructDefault($this);
        $this->addConfiguration($config);
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
     * @var string
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var integer
     * @ORM\Column(name="default_capacity", type="integer")
     */
    private $defaultCapacity;

    /**
     * @var Address
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Organization", inversedBy="venues", cascade={"persist"})
     */
    private $organization;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\VenueConfig", mappedBy="venue", cascade={"all"})
     */
    private $configurations;

    /**
     * @var
     * @ORM\Column(name="accept_conditions", type="boolean")
     */
    protected $accept_conditions;

    /**
     * @var
     * @ORM\Column(name="has_legal_manager", type="boolean")
     */
    protected $accept_being_responsible;

    /**
     * @var
     * @ORM\Column(name="is_temp", type="boolean")
     */
    protected $accept_venue_temp;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBContractArtist", mappedBy="venue", cascade={"all"})
     */
    private $events;

    /**
     * @var VenueConfig
     */
    private $defaultConfig;

    private $displayName;

    // getters & setters

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

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
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return mixed
     */
    public function getAcceptConditions()
    {
        return $this->accept_conditions;
    }

    /**
     * @param mixed $accept_conditions
     */
    public function setAcceptConditions($accept_conditions)
    {
        $this->accept_conditions = $accept_conditions;
    }

    /**
     * @return ArrayCollection
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * @return mixed
     */
    public function getAcceptBeingResponsible()
    {
        return $this->accept_being_responsible;
    }

    /**
     * @param mixed $accept_being_responsible
     */
    public function setAcceptBeingResponsible($accept_being_responsible)
    {
        $this->accept_being_responsible = $accept_being_responsible;
    }

    /**
     * @return mixed
     */
    public function getAcceptVenueTemp()
    {
        return $this->accept_venue_temp;
    }

    /**
     * @param mixed $accept_venue_temp
     */
    public function setAcceptVenueTemp($accept_venue_temp)
    {
        $this->accept_venue_temp = $accept_venue_temp;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getDefaultCapacity()
    {
        return $this->defaultCapacity;
    }

    /**
     * @param int $defaultCapacity
     */
    public function setDefaultCapacity($defaultCapacity)
    {
        $this->defaultCapacity = $defaultCapacity;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * @return VenueConfig
     */
    public function getDefaultConfig()
    {
        return $this->defaultConfig;
    }

    /**
     * @param VenueConfig $defaultConfig
     */
    public function setDefaultConfig($defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * @param mixed $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName(){
        return $this->displayName;
    }

}