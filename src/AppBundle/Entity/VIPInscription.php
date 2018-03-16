<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** VIPInscription
 *
 * @ORM\Table(name="vip_inscription")
 * @ORM\Entity
 **/
class VIPInscription implements PhysicalPersonInterface
{
    public function __construct()
    {
        $this->counterparts_sent = false;
    }

    public function __toString()
    {
        return $this->getDisplayName() . ' (' . $this->function . ' chez ' . $this->company . ')';
    }

    public function getDisplayName() {
        return $this->first_name . ' ' . strtoupper($this->last_name) . ' (' . $this->company . ')';
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
     * @ORM\ManyToOne(targetEntity="BaseContractArtist", inversedBy="vip_inscriptions")
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
     * @ORM\Column(name="company", type="string", length=255)
     */
    private $company;

    /**
     * @ORM\Column(name="function", type="string", length=255)
     */
    private $function;

    /**
     * @ORM\Column(name="counterparts_sent", type="boolean")
     */
    private $counterparts_sent;

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
     * @return VIPInscription
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
     * @return VIPInscription
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
     * @return VIPInscription
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
     * Set company
     *
     * @param string $company
     *
     * @return VIPInscription
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set function
     *
     * @param string $function
     *
     * @return VIPInscription
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set contractArtist
     *
     * @param \AppBundle\Entity\BaseContractArtist $contractArtist
     *
     * @return VIPInscription
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
     * @return VIPInscription
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
}
