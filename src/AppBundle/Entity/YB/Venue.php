<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Venue
 * @ORM\table(name="yb_venues")
 * @ORM\Entity
 */
class Venue {

    public function __construct(){
        $this->hasFreeSeating = false;
        $this->hasStandUpZone = false;
        $this->hasSeats = false;
        $this->hasBalcony = false;
        $this->hasPMRZone = false;
        $this->phoneNumberPMR = '';
        $this->emailAddressPMR = '';
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
     * @ORM\Column(name="hasFreeSeating", type="boolean")
     */
    private $hasFreeSeating;

    /**
     * @ORM\Column(name="hasStandUpZone", type="boolean")
     */
    private $hasStandUpZone;

    /**
     * @ORM\Column(name="hasSeats", type="boolean")
     */
    private $hasSeats;

    /**
     * @ORM\Column(name="hasBalcony", type="boolean")
     */
    private $hasBalcony;

    /**
     * @ORM\Column(name="hasPMRZone", type="boolean")
     */
    private $hasPMRZone;

    /**
     * @ORM\Column(name="phoneNumberPMR", type="string", length=20)
     */
    private $phoneNumberPMR;

    /**
     * @ORM\Column(name="emailAddressPMR", type="string", length=50)
     */
    private $emailAddressPMR;

    // getters & setters

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
     * @return mixed
     */
    public function getHasFreeSeating()
    {
        return $this->hasFreeSeating;
    }

    /**
     * @param mixed $hasFreeSeating
     */
    public function setHasFreeSeating($hasFreeSeating)
    {
        $this->hasFreeSeating = $hasFreeSeating;
    }

    /**
     * @return mixed
     */
    public function getHasStandUpZone()
    {
        return $this->hasStandUpZone;
    }

    /**
     * @param mixed $hasStandUpZone
     */
    public function setHasStandUpZone($hasStandUpZone)
    {
        $this->hasStandUpZone = $hasStandUpZone;
    }

    /**
     * @return mixed
     */
    public function getHasSeats()
    {
        return $this->hasSeats;
    }

    /**
     * @param mixed $hasSeats
     */
    public function setHasSeats($hasSeats)
    {
        $this->hasSeats = $hasSeats;
    }

    /**
     * @return mixed
     */
    public function getHasBalcony()
    {
        return $this->hasBalcony;
    }

    /**
     * @param mixed $hasBalcony
     */
    public function setHasBalcony($hasBalcony)
    {
        $this->hasBalcony = $hasBalcony;
    }

    /**
     * @return mixed
     */
    public function getHasPMRZone()
    {
        return $this->hasPMRZone;
    }

    /**
     * @param mixed $hasPMRZone
     */
    public function setHasPMRZone($hasPMRZone)
    {
        $this->hasPMRZone = $hasPMRZone;
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



}