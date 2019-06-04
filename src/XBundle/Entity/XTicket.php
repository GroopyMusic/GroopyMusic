<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XTicket
 *
 * @ORM\Table(name="x_ticket")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XTicketRepository")
 */
class XTicket
{

    public function __construct($cf, $product, $num, $price){
        $this->contractFan = $cf;
        $this->product = $product;
        $this->price = $price;
        $this->validated = false;
        $this->barcodeText = $cf->getBarcodeText() . '' . $num;
        $this->project = $cf->getProject();
        $this->name = $cf->getDisplayName();
    }

    public function __toString() {
        return $this->id . ' - ' . $this->name . '- Prix : ' . $this->price; 
    }

    /**
     * Check if ticket is validated
     * @return bool
     */
    public function isValidated()
    {
        return $this->getValidated();
    }

    /**
     * Check if contractFan is refunded
     * @return bool
     */
    public function isRefunded()
    {
        return $this->contractFan->getRefunded();
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
     * @var XContractFan
     * 
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XContractFan", inversedBy="tickets")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contractFan;

    /**
     * @var Product
     * 
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Product")
     */
    private $product;

    /**
     * @var Project
     * 
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project")
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode_text", type="string", length=255)
     */
    private $barcodeText;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;


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
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return XTicket
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcodeText = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcodeText;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return XTicket
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return XTicket
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
     * Set validated
     *
     * @param boolean $validated
     *
     * @return XTicket
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated
     *
     * @return bool
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Set contractFan
     *
     * @param \XBundle\Entity\XContractFan $contractFan
     *
     * @return XTicket
     */
    public function setContractFan(\XBundle\Entity\XContractFan $contractFan = null)
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
     * @return XTicket
     */
    public function setProduct(\XBundle\Entity\Product $product = null)
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
     * Set project
     *
     * @param \XBundle\Entity\Project $project
     *
     * @return XTicket
     */
    public function setProject(\XBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \XBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
