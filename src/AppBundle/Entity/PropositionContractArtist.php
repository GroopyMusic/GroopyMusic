<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * PropositionContractArtist
 *
 * @ORM\Table(name="proposition_contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PropositionContractArtistRepository")
 */
class PropositionContractArtist
{

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return "";
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
     * @var Province
     * @ORM\ManyToOne(targetEntity="Province",cascade={"persist"})
     */
    private $province;

    /**
     * @var PropositionHall
     * @ORM\OneToOne(targetEntity="PropositionHall",cascade={"persist"})
     */
    private $propositionHall;

    /**
     * @var PropositionArtist
     * @ORM\OneToOne(targetEntity="PropositionArtist",cascade={"persist"})
     */
    private $propositionArtist;

    /**
     * @var Artist
     * @ORM\ManyToOne(targetEntity="Artist",cascade={"persist"})
     */
    private $Artist;

    /**
     * @var ContactPerson
     * @ORM\OneToOne(targetEntity="ContactPerson",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $contactPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text")
     */
    private $reason;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_expected", type="integer")
     */
    private $nb_expected;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payable", type="boolean")
     */
    private $payable;

    /**
     * @ORM\Column(name="period_start_date", type="date")
     */
    private $period_start_date;

    /**
     * @ORM\Column(name="period_end_date", type="date", nullable=true)
     */
    private $period_end_date;

    /**
     * @var string
     *
     * @ORM\Column(name="day_commentary", type="text")
     */
    private $day_commentary;

    /**
     * @var string
     *
     * @ORM\Column(name="commentary", type="text")
     */
    private $commentary;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return PropositionContractArtist
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set nbExpected
     *
     * @param integer $nbExpected
     *
     * @return PropositionContractArtist
     */
    public function setNbExpected($nbExpected)
    {
        $this->nb_expected = $nbExpected;

        return $this;
    }

    /**
     * Get nbExpected
     *
     * @return integer
     */
    public function getNbExpected()
    {
        return $this->nb_expected;
    }

    /**
     * Set payable
     *
     * @param boolean $payable
     *
     * @return PropositionContractArtist
     */
    public function setPayable($payable)
    {
        $this->payable = $payable;

        return $this;
    }

    /**
     * Get payable
     *
     * @return boolean
     */
    public function getPayable()
    {
        return $this->payable;
    }

    /**
     * Set periodStartDate
     *
     * @param \DateTime $periodStartDate
     *
     * @return PropositionContractArtist
     */
    public function setPeriodStartDate($periodStartDate)
    {
        $this->period_start_date = $periodStartDate;

        return $this;
    }

    /**
     * Get periodStartDate
     *
     * @return \DateTime
     */
    public function getPeriodStartDate()
    {
        return $this->period_start_date;
    }

    /**
     * Set periodEndDate
     *
     * @param \DateTime $periodEndDate
     *
     * @return PropositionContractArtist
     */
    public function setPeriodEndDate($periodEndDate)
    {
        $this->period_end_date = $periodEndDate;

        return $this;
    }

    /**
     * Get periodEndDate
     *
     * @return \DateTime
     */
    public function getPeriodEndDate()
    {
        return $this->period_end_date;
    }

    /**
     * Set dayCommentary
     *
     * @param string $dayCommentary
     *
     * @return PropositionContractArtist
     */
    public function setDayCommentary($dayCommentary)
    {
        $this->day_commentary = $dayCommentary;

        return $this;
    }

    /**
     * Get dayCommentary
     *
     * @return string
     */
    public function getDayCommentary()
    {
        return $this->day_commentary;
    }

    /**
     * Set commentary
     *
     * @param string $commentary
     *
     * @return PropositionContractArtist
     */
    public function setCommentary($commentary)
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * Get commentary
     *
     * @return string
     */
    public function getCommentary()
    {
        return $this->commentary;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return PropositionContractArtist
     */
    public function setProvince(\AppBundle\Entity\Province $province = null)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return \AppBundle\Entity\Province
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set propositionHall
     *
     * @param \AppBundle\Entity\PropositionHall $propositionHall
     *
     * @return PropositionContractArtist
     */
    public function setPropositionHall(\AppBundle\Entity\PropositionHall $propositionHall = null)
    {
        $this->propositionHall = $propositionHall;

        return $this;
    }

    /**
     * Get propositionHall
     *
     * @return \AppBundle\Entity\PropositionHall
     */
    public function getPropositionHall()
    {
        return $this->propositionHall;
    }

    /**
     * Set propositionArtist
     *
     * @param \AppBundle\Entity\PropositionArtist $propositionArtist
     *
     * @return PropositionContractArtist
     */
    public function setPropositionArtist(\AppBundle\Entity\PropositionArtist $propositionArtist = null)
    {
        $this->propositionArtist = $propositionArtist;

        return $this;
    }

    /**
     * Get propositionArtist
     *
     * @return \AppBundle\Entity\PropositionArtist
     */
    public function getPropositionArtist()
    {
        return $this->propositionArtist;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return PropositionContractArtist
     */
    public function setArtist(\AppBundle\Entity\Artist $artist = null)
    {
        $this->Artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return \AppBundle\Entity\Artist
     */
    public function getArtist()
    {
        return $this->Artist;
    }

    /**
     * Set contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     *
     * @return PropositionContractArtist
     */
    public function setContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    /**
     * Get contactPerson
     *
     * @return \AppBundle\Entity\ContactPerson
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }
}
