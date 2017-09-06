<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * SpecialAdvantage
 *
 * @ORM\Table(name="special_advantage")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialAdvantageRepository")
 */
class SpecialAdvantage implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __toString()
    {
        return '' . $this->getName();
    }

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    public function getDefaultLocale() {
        return 'fr';
    }

    public function __construct()
    {
        $this->available = false;
        $this->availableQuantity = 0;
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

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
}
