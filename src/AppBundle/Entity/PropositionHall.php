<?php

namespace AppBundle\Entity;

/**
 * PropositionHall
 *
 * @ORM\Table(name="proposition_hall")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PropositionHallRepository")
 */
class PropositionHall
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->name;
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_email", type="string", length=255)
     */
    private $contact_email;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_phone", type="string", length=20)
     */
    private $contact_phone;

    /**
     * @var Province
     * @ORM\ManyToOne(targetEntity="Province")
     */
    private $province;

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
     * Set name
     *
     * @param string $name
     *
     * @return PropositionHall
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     *
     * @return PropositionHall
     */
    public function setContactEmail($contactEmail)
    {
        $this->contact_email = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }

    /**
     * Set contactPhone
     *
     * @param string $contactPhone
     *
     * @return PropositionHall
     */
    public function setContactPhone($contactPhone)
    {
        $this->contact_phone = $contactPhone;

        return $this;
    }

    /**
     * Get contactPhone
     *
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contact_phone;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return PropositionHall
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
}
