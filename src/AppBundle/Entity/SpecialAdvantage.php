<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SpecialAdvantage
 *
 * @ORM\Table(name="special_advantage")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialAdvantageRepository")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translations\SpecialAdvantageTranslation")
 */
class SpecialAdvantage extends AbstractPersonalTranslatable implements TranslatableInterface
{
    public function __construct()
    {
        $this->available = false;
        $this->availableQuantity = 0;
    }

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Translations\SpecialAdvantageTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $translations;

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
     * @Gedmo\Translatable
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Gedmo\Translatable
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="available_quantity", type="integer")
     */
    private $availableQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="price_credits", type="integer")
     */
    private $priceCredits;

    /**
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

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
     * @return SpecialAdvantage
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
     * Set description
     *
     * @param string $description
     *
     * @return SpecialAdvantage
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
     * Set priceCredits
     *
     * @param integer $priceCredits
     *
     * @return SpecialAdvantage
     */
    public function setPriceCredits($priceCredits)
    {
        $this->priceCredits = $priceCredits;

        return $this;
    }

    /**
     * Get priceCredits
     *
     * @return int
     */
    public function getPriceCredits()
    {
        return $this->priceCredits;
    }

    /**
     * Set availableQuantity
     *
     * @param integer $availableQuantity
     *
     * @return SpecialAdvantage
     */
    public function setAvailableQuantity($availableQuantity)
    {
        $this->availableQuantity = $availableQuantity;

        return $this;
    }

    /**
     * Get availableQuantity
     *
     * @return integer
     */
    public function getAvailableQuantity()
    {
        return $this->availableQuantity;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return SpecialAdvantage
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available
     *
     * @return boolean
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * Remove translation
     *
     * @param \AppBundle\Entity\Translations\SpecialAdvantageTranslation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Translations\SpecialAdvantageTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }
}
