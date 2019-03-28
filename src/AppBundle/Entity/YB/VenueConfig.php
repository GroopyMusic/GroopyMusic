<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Venue
 * @ORM\table(name="yb_venues_config")
 * @ORM\Entity
 */
class VenueConfig {

    public function __construct(){
        $this->maxCapacity = 0;
        $this->nbStandUp = 0;
        $this->nbSeatedSeats = 0;
        $this->nbBalconySeats = 0;
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
     * @var Venue
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Venue", inversedBy="configurations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $venue;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\Block", mappedBy="config", cascade={"persist"})
     */
    private $blocks;

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

    /**
     * @param mixed $blocks
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

}