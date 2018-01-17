<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

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
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\Sluggable\Sluggable;

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch(\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function getDefaultLocale() {
        return 'fr';
    }

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
    }

    public function getSluggableFields() {
        return ['name'];
    }

    public function getType() {
        if($this instanceof Hall) {
            return 'hall';
        }
    }

    public function getWebsiteDomain() {
        $pieces = parse_url($this->website);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return 'www.' . $regs['domain'];
        }
        return $this->website;
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
        $this->visible = true;
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
     * @ORM\Column(name="visible", type="boolean")
     */
    protected $visible;

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

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return Partner
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }
}
