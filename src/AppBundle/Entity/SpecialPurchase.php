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
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getAmountCredits() {
        return $this->quantity * $this->specialAdvantage->getPriceCredits();
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="specialPurchases")
     */
    private $user;

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

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return SpecialPurchase
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
