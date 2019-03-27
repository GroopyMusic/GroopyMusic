<?php

namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Venue
 * @ORM\table(name="yb_venues")
 */
class Venue {

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

}