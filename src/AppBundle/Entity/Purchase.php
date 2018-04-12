<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
        return $this->counterpart . ' (x'.$this->quantity.')' . $this->getActuallyAppliedPromotionsString() ;
    }

    public function __construct()
    {
        $this->quantity = 0;
        $this->nb_free_counterparts = 0;
        $this->reducedPrice = 0;
        $this->purchase_promotions = new ArrayCollection();
    }

    public function getPromotions()
    {
        return array_map(function(Purchase_Promotion $p_promotion) {
            return $p_promotion->getPromotion();
        }, $this->purchase_promotions->toArray());
    }

    public function getActuallyAppliedPromotions() {
        return array_map(function(Purchase_Promotion $p_promotion) {
                return $p_promotion->getPromotion();
            }, array_filter($this->purchase_promotions->toArray(), function(Purchase_Promotion $p_promotion) {
                return $p_promotion->getNbFreeCounterParts() > 0;
            }));
    }

    public function getActuallyAppliedPromotionsString() {
        $string = '';
        foreach($this->getActuallyAppliedPromotions() as $promotion) {
            $string .= ' - ' . $promotion;
        }
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

    /**
     * @return ContractArtist
     */
    public function getContractArtist() {
        return $this->contractFan->getContractArtist();
    }

    public function calculatePromotions() {
        foreach($this->getContractArtist()->getPromotions() as $promotion) {
            /** @var Promotion $promotion */
            if($this->contractFan->isEligibleForPromotion($promotion) && !in_array($promotion, $this->getActuallyAppliedPromotions())) {
                $new_promotional_counterparts = $promotion->getNbPromotional() * (floor($this->getQuantityOrganic() / $promotion->getNbOrganicNeeded()));
                $this->nb_free_counterparts += $new_promotional_counterparts;
                $this->addQuantity($new_promotional_counterparts);
                $pp = new Purchase_Promotion($this, $promotion, $new_promotional_counterparts);
                $this->addPurchasePromotion($pp);
            }
        }
    }

    public function getQuantityOrganic() {
        return $this->quantity - $this->getQuantityPromotional();
    }

    public function getQuantityPromotional() {
        return $this->getNbFreeCounterparts();
    }

    public function getNbFreeCounterparts() {
        return $this->nb_free_counterparts;
    }

    public function getReducedAmount(){
        return $this->getReducedPrice() * $this->getQuantityOrganic();
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
     * @var ContractFan
     *
     * @ORM\ManyToOne(targetEntity="ContractFan", inversedBy="purchases")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contractFan;

    /**
     * @var CounterPart
     *
     * @ORM\ManyToOne(targetEntity="CounterPart")
     * @ORM\JoinColumn(nullable=false)
     */
    private $counterpart;

    /**
     * @ORM\Column(name="nb_free_counterparts", type="smallint")
     */
    private $nb_free_counterparts;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Purchase_Promotion", mappedBy="purchase", cascade={"all"})
     */
    private $purchase_promotions;

    /**
     * @ORM\Column(name="reducedPrice", type="float", nullable=true)
     */
    private $reducedPrice;

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

    /**
     * Add purchasePromotion
     *
     * @param \AppBundle\Entity\Purchase_Promotion $purchasePromotion
     *
     * @return Purchase
     */
    public function addPurchasePromotion(\AppBundle\Entity\Purchase_Promotion $purchasePromotion)
    {
        $this->purchase_promotions[] = $purchasePromotion;

        return $this;
    }

    /**
     * Remove purchasePromotion
     *
     * @param \AppBundle\Entity\Purchase_Promotion $purchasePromotion
     */
    public function removePurchasePromotion(\AppBundle\Entity\Purchase_Promotion $purchasePromotion)
    {
        $this->purchase_promotions->removeElement($purchasePromotion);
    }

    /**
     * Get purchasePromotions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchasePromotions()
    {
        return $this->purchase_promotions;
    }

    /**
     * Set reducedPrice
     *
     * @param float $reducedPrice
     *
     * @return Purchase
     */
    public function setReducedPrice($reducedPrice)
    {
        $this->reducedPrice = $reducedPrice;

        return $this;
    }

    /**
     * Get reducedPrice
     *
     * @return float
     */
    public function getReducedPrice()
    {
        return $this->reducedPrice;
    }
}
