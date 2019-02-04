<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Purchase_Promotion
 *
 * @ORM\Table(name="purchase__promotion")
 * @ORM\Entity()
 */

class Purchase_Promotion
{
    public function __construct(Purchase $purchase = null, Promotion $promotion = null, $nb_free_counterparts = null, $toppings = [])
    {
        $this->purchase = $purchase;
        $this->promotion = $promotion;
        $this->nb_free_counterparts = $nb_free_counterparts;
        $this->toppings = new ArrayCollection();
        $this->setToppings(new ArrayCollection($toppings));
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
     * @var Purchase
     * @ORM\ManyToOne(targetEntity="Purchase", inversedBy="purchase_promotions")
     */
    private $purchase;

    /**
     * @var Promotion
     * @ORM\ManyToOne(targetEntity="Promotion")
     */
    private $promotion;

    /**
     * @var int
     * @ORM\Column(name="nb_free_counterparts", type="smallint")
     */
    private $nb_free_counterparts;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Topping", mappedBy="purchase_promotion", cascade={"all"})
     */
    private $toppings;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nbFreeCounterparts
     *
     * @param integer $nbFreeCounterparts
     *
     * @return Purchase_Promotion
     */
    public function setNbFreeCounterparts($nbFreeCounterparts)
    {
        $this->nb_free_counterparts = $nbFreeCounterparts;

        return $this;
    }

    /**
     * Get nbFreeCounterparts
     *
     * @return integer
     */
    public function getNbFreeCounterparts()
    {
        return $this->nb_free_counterparts;
    }

    /**
     * Set purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     *
     * @return Purchase_Promotion
     */
    public function setPurchase(\AppBundle\Entity\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \AppBundle\Entity\Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set promotion
     *
     * @param \AppBundle\Entity\Promotion $promotion
     *
     * @return Purchase_Promotion
     */
    public function setPromotion(\AppBundle\Entity\Promotion $promotion = null)
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * Get promotion
     *
     * @return \AppBundle\Entity\Promotion
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * @return ArrayCollection
     */
    public function getToppings()
    {
        return $this->toppings;
    }

    /**
     * @param ArrayCollection $toppings
     * @return $this
     */
    public function setToppings(ArrayCollection $toppings)
    {
        $this->toppings->clear();
        foreach($toppings as $topping) {
            $this->addTopping($topping);
        }
        return $this;
    }

    /**
     * @param Topping $topping
     * @return $this
     */
    public function addTopping(Topping $topping) {
        $this->toppings->add($topping);
        $topping->setPurchasePromotion($this);
        return $this;
    }

    /**
     * @param Topping $topping
     * @return $this
     */
    public function RemoveTopping(Topping $topping) {
        $this->toppings->remove($topping);
        return $this;
    }
}
