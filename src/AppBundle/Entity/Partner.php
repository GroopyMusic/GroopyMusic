<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partner
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PartnerRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"partner" = "Partner", "hall" = "Hall"})
 *
 */
class Partner
{
    public function getType() {
        if($this instanceof Hall) {
            return 'hall';
        }
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    protected $website;

    /**
     * @ORM\ManyToMany(targetEntity="ContactPerson", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $contact_person;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Province")
     */
    protected $province;

    /**
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

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
     * Set name
     *
     * @param string $name
     *
     * @return Partner
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
     * Set website
     *
     * @param string $website
     *
     * @return Partner
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Partner
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return Partner
     */
    public function setAddress(\AppBundle\Entity\Address $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     *
     * @return Partner
     */
    public function setContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contact_person = $contactPerson;

        return $this;
    }

    /**
     * Get contactPerson
     *
     * @return \AppBundle\Entity\ContactPerson
     */
    public function getContactPerson()
    {
        return $this->contact_person;
    }

    /**
     * Set province
     *
     * @param \AppBundle\Entity\Province $province
     *
     * @return Partner
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
     * Set description
     *
     * @param string $description
     *
     * @return Partner
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contact_person = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     *
     * @return Partner
     */
    public function addContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contact_person[] = $contactPerson;

        return $this;
    }

    /**
     * Remove contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     */
    public function removeContactPerson(\AppBundle\Entity\ContactPerson $contactPerson)
    {
        $this->contact_person->removeElement($contactPerson);
    }
}
