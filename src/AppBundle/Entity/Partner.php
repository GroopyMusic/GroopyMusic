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

    public function getContactPersons() {
        return array_map(function($elem) {
            return $elem->getContactPerson();
        }, $this->contact_persons_list->toArray());
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contact_persons_list = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\OneToMany(targetEntity="Partner_ContactPerson", mappedBy="partner", cascade={"all"}, orphanRemoval=true)
     */
    protected $contactpersons_list;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Province")
     */
    protected $province;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
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
     * Add contactpersonsList
     *
     * @param \AppBundle\Entity\Partner_ContactPerson $contactpersonsList
     *
     * @return Partner
     */
    public function addContactpersonsList(\AppBundle\Entity\Partner_ContactPerson $contactpersonsList)
    {
        $this->contactpersons_list[] = $contactpersonsList;
        $contactpersonsList->setPartner($this);

        return $this;
    }

    /**
     * Remove contactpersonsList
     *
     * @param \AppBundle\Entity\Partner_ContactPerson $contactpersonsList
     */
    public function removeContactpersonsList(\AppBundle\Entity\Partner_ContactPerson $contactpersonsList)
    {
        $this->contactpersons_list->removeElement($contactpersonsList);
    }

    /**
     * Get contactpersonsList
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactpersonsList()
    {
        return $this->contactpersons_list;
    }
}
