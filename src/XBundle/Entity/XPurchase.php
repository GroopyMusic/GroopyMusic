<?php

namespace XBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * XPurchase
 *
 * @ORM\Table(name="x_purchase")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XPurchaseRepository")
 */
class XPurchase
{

    public function __construct()
    {
        $this->quantity = 0;
        $this->choices = new ArrayCollection();
    }


    public function __toString()
    {
        $str = '';

        $str .= $this->product . ' (x' . $this->quantity . ') - ' . $this->getAmount() . ' €';

        if(count($this->choices) > 0) {
            $str .= ' - (';
            for ($i=0; $i < count($this->choices); $i++) {
                $str .= $this->choices[$i];
                if ($i != count($this->choices)-1) {
                    $str .= '/';
                }
            }
            $str .= ')';
        }

        return $str;
    }

    public function getAmount()
    {
        return $this->quantity * $this->getUnitaryPrice() ;
    }

    public function getUnitaryPrice() {
        if($this->product->getFreePrice()) {
            return $this->freePrice;
        }
        else {
            return $this->product->getPrice();
        }
    }

    public function getProject()
    {
        return $this->product->getProject();
    }

    public function getOptions() {
        return $this->product->getOptions();
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
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XContractFan", inversedBy="purchases")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contractFan;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Product")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="free_price", type="float", nullable=true)
     */
    private $freePrice;

    /**
     * @ORM\ManyToMany(targetEntity="XBundle\Entity\ChoiceOption")
     */
    private $choices;


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
     * @return XPurchase
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
        return $this->quantity;
    }

    /**
     * Set freePrice
     *
     * @param float $freePrice
     *
     * @return XPurchase
     */
    public function setFreePrice($freePrice)
    {
        $this->freePrice = $freePrice;

        return $this;
    }

    /**
     * Get freePrice
     *
     * @return float
     */
    public function getFreePrice()
    {
        return $this->freePrice;
    }

    /**
     * Set contractFan
     *
     * @param \XBundle\Entity\XContractFan $contractFan
     *
     * @return XPurchase
     */
    public function setContractFan(\XBundle\Entity\XContractFan $contractFan)
    {
        $this->contractFan = $contractFan;

        return $this;
    }

    /**
     * Get contractFan
     *
     * @return \XBundle\Entity\XContractFan
     */
    public function getContractFan()
    {
        return $this->contractFan;
    }

    /**
     * Set product
     *
     * @param \XBundle\Entity\Product $product
     *
     * @return XPurchase
     */
    public function setProduct(\XBundle\Entity\Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \XBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Add choice
     *
     * @param \XBundle\Entity\ChoiceOption $choice
     *
     * @return XPurchase
     */
    public function addChoice(\XBundle\Entity\ChoiceOption $choice)
    {
        $this->choices[] = $choice;

        return $this;
    }

    /**
     * Remove choice
     *
     * @param \XBundle\Entity\ChoiceOption $choice
     */
    public function removeChoice(\XBundle\Entity\ChoiceOption $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
