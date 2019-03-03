<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XCart
 *
 * @ORM\Table(name="x_cart")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XCartRepository")
 */
class XCart
{

    public function generateBarCode()
    {
        if (empty($this->barcode_text)) {

            $str = 'x';

            $str .= 'c' . $this->id . uniqid();

            $this->barcode_text = $str;
        }
        return $this->barcode_text;
    }

    private $um = true;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XPayUserInfo", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $user_info;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Projects", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $projects;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Product", cascade={"persist"})
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(name="donation_amount", type="float", nullable=true)
     */
    private $donation_amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="product_quantity", type="integer", nullable=true)
     */
    private $product_quantity;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XPayUserInfo")
     * @ORM\JoinColumn(nullable=false)
     */

    /**
     * @var bool
     *
     * @ORM\Column(name="confirmed", type="boolean")
     */
    private $confirmed;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @var string
     * @ORM\Column(name="barcode_text", type="string", length=255, nullable=true)
     */
    private $barcode_text;


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
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return XCart
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return bool
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return XCart
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set donationAmount
     *
     * @param float $donationAmount
     *
     * @return XCart
     */
    public function setDonationAmount($donationAmount)
    {
        $this->donation_amount = $donationAmount;

        return $this;
    }

    /**
     * Get donationAmount
     *
     * @return float
     */
    public function getDonationAmount()
    {
        return $this->donation_amount;
    }

    /**
     * Set userInfo
     *
     * @param \XBundle\Entity\XPayUserInfo $userInfo
     *
     * @return XCart
     */
    public function setUserInfo(\XBundle\Entity\XPayUserInfo $userInfo)
    {
        $this->user_info = $userInfo;

        return $this;
    }

    /**
     * Get userInfo
     *
     * @return \XBundle\Entity\XPayUserInfo
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return XCart
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcode_text = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcode_text;
    }

    /**
     * Set productQuantity
     *
     * @param integer $productQuantity
     *
     * @return XCart
     */
    public function setProductQuantity($productQuantity)
    {
        $this->product_quantity = $productQuantity;

        return $this;
    }

    /**
     * Get productQuantity
     *
     * @return integer
     */
    public function getProductQuantity()
    {
        return $this->product_quantity;
    }



    /**
     * Set product
     *
     * @param \XBundle\Entity\Product $product
     *
     * @return XCart
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
     * Set projects
     *
     * @param \XBundle\Entity\Projects $projects
     *
     * @return XCart
     */
    public function setProjects(\XBundle\Entity\Projects $projects)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * Get projects
     *
     * @return \XBundle\Entity\Projects
     */
    public function getProjects()
    {
        return $this->projects;
    }
}
