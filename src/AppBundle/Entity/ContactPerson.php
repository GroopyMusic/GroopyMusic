<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactPerson
 *
 * @ORM\Table(name="contact_person")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContactPersonRepository")
 */
class ContactPerson
{
    public function __toString()
    {
        return $this->getDisplayName() . ' (Tél : ' . $this->phone . ' , mail : ' . $this->getMail() . ')';
    }

    public function __construct()
    {
        $this->partners_list = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPartners() {
        return array_map(function($elem) {
            /** @var Partner_ContactPerson $elem */
            return $elem->getPartner();
        }, $this->partners_list->toArray());
    }


    public function getDisplayName() {
        return $this->getFirstname() . ' ' . $this->getLastname();
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
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255)
     */
    private $mail;

    /**
     * @ORM\OneToMany(targetEntity="Partner_ContactPerson", mappedBy="contact_person", cascade={"persist"}, orphanRemoval=true)
     */
    protected $partners_list;

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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return ContactPerson
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return ContactPerson
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return ContactPerson
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mail
     *
     * @param string $mail
     *
     * @return ContactPerson
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Add partnersList
     *
     * @param \AppBundle\Entity\Partner_ContactPerson $partnersList
     *
     * @return ContactPerson
     */
    public function addPartnersList(\AppBundle\Entity\Partner_ContactPerson $partnersList)
    {
        $this->partners_list[] = $partnersList;
        $partnersList->setPartner($this);

        return $this;
    }

    /**
     * Remove partnersList
     *
     * @param \AppBundle\Entity\Partner_ContactPerson $partnersList
     */
    public function removePartnersList(\AppBundle\Entity\Partner_ContactPerson $partnersList)
    {
        $this->partners_list->removeElement($partnersList);
    }

    /**
     * Get partnersList
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPartnersList()
    {
        return $this->partners_list;
    }

}
