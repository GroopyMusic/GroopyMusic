<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * Class Venue
 * @ORM\table(name="yb_venues")
 * @ORM\Entity
 */
class Venue {

    use ORMBehaviors\SoftDeletable\SoftDeletable;

    public function __construct(){
        $this->configurations = new ArrayCollection();
    }

    public function addConfiguration(VenueConfig $config){
        $config->setVenue($this);
        $this->configurations->add($config);
    }

    public function removeConfiguration(VenueConfig $config){
        $config->setVenue(null);
        $this->configurations->removeElement($config);
    }

    public function getHandlers(){
        return $this->organization->getMembers();
    }

    public function isHandledByUser(User $user){
        return in_array($user, $this->organization->getMembers());
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
     * @var Address
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Organization", inversedBy="venue", cascade={"persist"})
     */
    private $organization;

    /**
     * @var VenueConfig
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\VenueConfig", mappedBy="venue", cascade={"all"})
     */
    private $configurations;

    protected $accept_conditions;
    protected $accept_being_responsible;
    protected $accept_venue_temp;

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
     * @return VenueConfig
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

}