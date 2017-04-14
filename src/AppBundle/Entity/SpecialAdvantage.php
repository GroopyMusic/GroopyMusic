<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialAdvantage
 *
 * @ORM\Table(name="special_advantage")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialAdvantageRepository")
 */
class SpecialAdvantage
{
    public function __construct()
    {
        $this->available = false;
        $this->availableQuantity = 0;
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
     * @ORM\Column(name="description", type="text")
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
}
