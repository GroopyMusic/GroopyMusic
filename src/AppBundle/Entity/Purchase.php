<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Purchase
 *
 * @ORM\Table(name="purchase")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PurchaseRepository")
 */
class Purchase
{
    const MAX_QTY = 100000;

    public function __toString()
    {
        return $this->counterpart . ' (x'.$this->quantity.')';
    }

    public function __construct()
    {
        $this->quantity = 0;
        $this->nb_free_counterparts = 0;
    }

    public function addQuantity($q) {
        $this->quantity = $this->quantity + $q;
        if($this->quantity > self::MAX_QTY) {
            $this->quantity = self::MAX_QTY;
        }
    }

    public function getAmount() {
        return $this->getQuantityOrganic() * $this->counterpart->getPrice();
    }

    public function calculatePromotions() {
        if($this->quantity >= 3 && $this->nb_free_counterparts == 0) {
            // TODO adapt ; this is for February 2018 promotion
            $this->nb_free_counterparts = floor($this->quantity / 3);
            $this->addQuantity($this->nb_free_counterparts);
        }
    }

    public function getQuantityOrganic() {
        if($this->quantity >= 3 && $this->nb_free_counterparts == 0) {
            $this->calculatePromotions();
        }

        return $this->quantity - $this->getQuantityPromotional();
    }

    public function getQuantityPromotional() {
        if($this->quantity >= 3 && $this->nb_free_counterparts == 0) {
            $this->calculatePromotions();
        }

        return $this->getNbFreeCounterparts();
    }

    public function getNbFreeCounterparts() {
        return $this->nb_free_counterparts;
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
     * @ORM\Column(name="quantity", type="smallint")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="ContractFan", inversedBy="purchases")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contractFan;

    /**
     * @ORM\ManyToOne(targetEntity="CounterPart")
     * @ORM\JoinColumn(nullable=false)
     */
    private $counterpart;

    /**
     * @ORM\Column(name="nb_free_counterparts", type="smallint")
     */
    private $nb_free_counterparts;

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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Purchase
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        if($this->quantity >= 3 && $this->nb_free_counterparts == 0) {
            $this->calculatePromotions();
        }
        return $this->quantity;
    }

    /**
     * Set contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     *
     * @return Purchase
     */
    public function setContractFan(\AppBundle\Entity\ContractFan $contractFan)
    {
        $this->contractFan = $contractFan;

        return $this;
    }

    /**
     * Get contractFan
     *
     * @return \AppBundle\Entity\ContractFan
     */
    public function getContractFan()
    {
        return $this->contractFan;
    }

    /**
     * Set counterpart
     *
     * @param \AppBundle\Entity\CounterPart $counterpart
     *
     * @return Purchase
     */
    public function setCounterpart(\AppBundle\Entity\CounterPart $counterpart)
    {
        $this->counterpart = $counterpart;

        return $this;
    }

    /**
     * Get counterpart
     *
     * @return \AppBundle\Entity\CounterPart
     */
    public function getCounterpart()
    {
        return $this->counterpart;
    }

    /**
     * Set nbFreeCounterparts
     *
     * @param integer $nbFreeCounterparts
     *
     * @return Purchase
     */
    public function setNbFreeCounterparts($nbFreeCounterparts)
    {
        $this->nb_free_counterparts = $nbFreeCounterparts;

        return $this;
    }
}
