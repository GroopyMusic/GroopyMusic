<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** VolunteerProposal
 *
 * @ORM\Table(name="volunteer_proposal")
 * @ORM\Entity
 **/
class VolunteerProposal implements PhysicalPersonInterface
{
    public function __construct()
    {
        $this->counterparts_sent = false;
    }

    public function __toString()
    {
        return $this->getDisplayName();
    }

    public function getDisplayName() {
        return $this->first_name . ' ' . strtoupper($this->last_name);
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
     * @var ContractArtist
     * @ORM\ManyToOne(targetEntity="BaseContractArtist", inversedBy="volunteer_proposals")
     */
    private $contract_artist;

    /**
     * @ORM\Column(name="last_name", type="string", length=63)
     */
    private $last_name;

    /**
     * @ORM\Column(name="first_name", type="string", length=63)
     */
    private $first_name;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(name="counterparts_sent", type="boolean")
     */
    private $counterparts_sent;

    /**
     * @ORM\Column(name="commentary", type="text", nullable=true)
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
     * Set lastName
     *
     * @param string $lastName
     *
     * @return VolunteerProposal
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return VolunteerProposal
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return VolunteerProposal
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
     * Set contractArtist
     *
     * @param \AppBundle\Entity\BaseContractArtist $contractArtist
     *
     * @return VolunteerProposal
     */
    public function setContractArtist(\AppBundle\Entity\BaseContractArtist $contractArtist = null)
    {
        $this->contract_artist = $contractArtist;

        return $this;
    }

    /**
     * Get contractArtist
     *
     * @return \AppBundle\Entity\BaseContractArtist
     */
    public function getContractArtist()
    {
        return $this->contract_artist;
    }

    /**
     * Set counterpartsSent
     *
     * @param boolean $counterpartsSent
     *
     * @return VolunteerProposal
     */
    public function setCounterpartsSent($counterpartsSent)
    {
        $this->counterparts_sent = $counterpartsSent;

        return $this;
    }

    /**
     * Get counterpartsSent
     *
     * @return boolean
     */
    public function getCounterpartsSent()
    {
        return $this->counterparts_sent;
    }

    /**
     * Set commentary
     *
     * @param string $commentary
     *
     * @return VolunteerProposal
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
}
