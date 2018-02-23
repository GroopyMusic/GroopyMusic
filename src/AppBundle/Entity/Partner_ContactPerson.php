<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtist_Artist
 *
 * @ORM\Table(name="partner__contact_person")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Partner_ContactPersonRepository")
 */
class Partner_ContactPerson
{
    public function __construct()
    {
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
     * @ORM\ManyToOne(targetEntity="ContactPerson", inversedBy="partners_list")
     */
    private $contact_person;

    /**
     * @ORM\ManyToOne(targetEntity="Partner", inversedBy="contactpersons_list")
     */
    private $partner;

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
     * Set contactPerson
     *
     * @param \AppBundle\Entity\ContactPerson $contactPerson
     *
     * @return Partner_ContactPerson
     */
    public function setContactPerson(\AppBundle\Entity\ContactPerson $contactPerson = null)
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
     * Set partner
     *
     * @param \AppBundle\Entity\Partner $partner
     *
     * @return Partner_ContactPerson
     */
    public function setPartner(\AppBundle\Entity\Partner $partner = null)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return \AppBundle\Entity\Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }
}
