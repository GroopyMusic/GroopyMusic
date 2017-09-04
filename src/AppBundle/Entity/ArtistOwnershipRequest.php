<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArtistOwnershipRequest
 *
 * @ORM\Table(name="artist_ownership_request")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistOwnershipRequestRepository")
 */
class ArtistOwnershipRequest
{
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->accepted = false;
        $this->refused = false;
        $this->cancelled = false;
    }

    public function generateUniqueCode() {
        return $this->code = uniqid($this->id);
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;

    /**
     * @var bool
     *
     * @ORM\Column(name="refused", type="boolean")
     */
    private $refused;

    /**
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="ownership_requests")
     */
    private $artist;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $demander;

    /**
     * @ORM\Column(name="cancelled", type="boolean")
     */
    private $cancelled;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ArtistOwnershipRequest
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return ArtistOwnershipRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return ArtistOwnershipRequest
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return bool
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set refused
     *
     * @param boolean $refused
     *
     * @return ArtistOwnershipRequest
     */
    public function setRefused($refused)
    {
        $this->refused = $refused;

        return $this;
    }

    /**
     * Get refused
     *
     * @return bool
     */
    public function getRefused()
    {
        return $this->refused;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return ArtistOwnershipRequest
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return ArtistOwnershipRequest
     */
    public function setArtist(\AppBundle\Entity\Artist $artist = null)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return \AppBundle\Entity\Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set demander
     *
     * @param \AppBundle\Entity\User $demander
     *
     * @return ArtistOwnershipRequest
     */
    public function setDemander(\AppBundle\Entity\User $demander = null)
    {
        $this->demander = $demander;

        return $this;
    }

    /**
     * Get demander
     *
     * @return \AppBundle\Entity\User
     */
    public function getDemander()
    {
        return $this->demander;
    }

    /**
     * Set cancelled
     *
     * @param boolean $cancelled
     *
     * @return ArtistOwnershipRequest
     */
    public function setCancelled($cancelled)
    {
        $this->cancelled = $cancelled;

        return $this;
    }

    /**
     * Get cancelled
     *
     * @return boolean
     */
    public function getCancelled()
    {
        return $this->cancelled;
    }
}
