<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="topping")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"string" = "ToppingString"})
 */
abstract class Topping {

    public function __construct()
    {
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Discriminator
     * @var string
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $barCodeText;

    /**
     * @var Purchase_Promotion
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Purchase_Promotion", inversedBy="toppings")
     */
    protected $purchase_promotion;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getBarCodeText() {
        return $this->barCodeText;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setBarCodeText($text) {
        $this->setBarCodeText($text);
        return $this;
    }

    /**
     * @return Purchase_Promotion
     */
    public function getPurchasePromotion() {
        return $this->purchase_promotion;
    }

    /**
     * @param Purchase_Promotion $purchase_promotion
     * @return $this
     */
    public function setPurchasePromotion($purchase_promotion)
    {
        $this->purchase_promotion = $purchase_promotion;
        return $this;
    }
}