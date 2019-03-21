<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use XBundle\Entity\Image;
use XBundle\Entity\Project;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ProductRepository")
 */
class Product
{

    const PHOTOS_DIR = 'x/images/projects/';

    public function __construct() {
        $this->freePrice = false;
        $this->minimumPrice = 0;
        $this->productsSold = 0;
    }

    public static function getWebPath(Image $image) {
        return self::PHOTOS_DIR . $image->getFilename();
    }

    public function addProductsSold($quantity) {
        $this->productsSold += $quantity;
    }

    public function updateSupply($quantity) {
        $this->supply -= $quantity;
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
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToOne(targetEntity="XBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $photo;

    /**
     * @var int
     * 
     * @ORM\Column(name="supply", type="integer")
     */
    private $supply;

    /**
     * @var bool
     * 
     * @ORM\Column(name="free_price", type="boolean")
     */
    private $freePrice;

    /**
     * @var float
     * 
     * @ORM\Column(name="minimum_price", type="float")
     */
    private $minimumPrice;

    /**
     * @var int
     *
     * @ORM\Column(name="products_sold", type="integer")
     */
    private $productsSold;


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
     * @return Product
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
     * @return Product
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
     * Set price
     *
     * @param float $price
     *
     * @return Product
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
     * Set project
     *
     * @param Project $project
     *
     * @return Product
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set photo
     *
     * @param Image $photo
     *
     * @return Product
     */
    public function setPhoto($photo = null)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return Image
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set stock
     * 
     * @param integer $stock
     * 
     * @return Product
     */
    public function setSupply($supply)
    {
        $this->supply = $supply;

        return $this;
    }

    /**
     * Get supply
     * 
     * @return int
     */
    public function getSupply()
    {
        return $this->supply;
    }

    /**
     * Set freePrice
     * 
     * @param bool $freePrice
     * 
     * @return Product
     */
    public function setFreePrice($freePrice)
    {
        $this->freePrice = $freePrice;

        return $this;
    }

    /**
     * Get freePrice
     * 
     * @return bool
     */
    public function getFreePrice()
    {
        return $this->freePrice;
    }

    /**
     * Set minimumPrice
     * 
     * @param float $minimumPrice
     * 
     * @return Product
     */
    public function setMinimumPrice($minimumPrice)
    {
        $this->minimumPrice = $minimumPrice;

        return $this;
    }

    /**
     * Get minimumPrice
     * 
     * @return float
     */
    public function getMinimumPrice()
    {
        return $this->minimumPrice;
    }

    /**
     * Set productsSold
     *
     * @param integer $productsSold
     *
     * @return Product
     */
    public function setProductsSold($productsSold)
    {
        $this->productsSold = $productsSold;

        return $this;
    }

    /**
     * Get productsSold
     *
     * @return int
     */
    public function getProductsSold()
    {
        return $this->productsSold;
    }
}

