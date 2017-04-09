<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialPurchase
 *
 * @ORM\Table(name="special_purchase")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SpecialPurchaseRepository")
 */
class SpecialPurchase
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="quantity", type="smallint")
     */
    private $quantity;

    /**
     * @ORM\Column(name="price_credits", type="integer")
     */
    private $price_credits;

    /**
     * @ORM\ManyToOne(targetEntity="UserFan", inversedBy="specialPurchases")
     */
    private $fan;

    /**
     * @ORM\ManyToOne(targetEntity="SpecialAdvantage")
     */
    private $specialAdvantage;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return SpecialPurchase
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return SpecialPurchase
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set priceCredits
     *
     * @param integer $priceCredits
     *
     * @return SpecialPurchase
     */
    public function setPriceCredits($priceCredits)
    {
        $this->price_credits = $priceCredits;

        return $this;
    }

    /**
     * Get priceCredits
     *
     * @return integer
     */
    public function getPriceCredits()
    {
        return $this->price_credits;
    }

    /**
     * Set fan
     *
     * @param \AppBundle\Entity\UserFan $fan
     *
     * @return SpecialPurchase
     */
    public function setFan(\AppBundle\Entity\UserFan $fan = null)
    {
        $this->fan = $fan;

        return $this;
    }

    /**
     * Get fan
     *
     * @return \AppBundle\Entity\UserFan
     */
    public function getFan()
    {
        return $this->fan;
    }

    /**
     * Set specialAdvantage
     *
     * @param \AppBundle\Entity\SpecialAdvantage $specialAdvantage
     *
     * @return SpecialPurchase
     */
    public function setSpecialAdvantage(\AppBundle\Entity\SpecialAdvantage $specialAdvantage = null)
    {
        $this->specialAdvantage = $specialAdvantage;

        return $this;
    }

    /**
     * Get specialAdvantage
     *
     * @return \AppBundle\Entity\SpecialAdvantage
     */
    public function getSpecialAdvantage()
    {
        return $this->specialAdvantage;
    }
}
