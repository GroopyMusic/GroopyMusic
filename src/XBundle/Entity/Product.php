<?php

namespace XBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use XBundle\Entity\Project;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ProductRepository")
 * @Vich\Uploadable
 */
class Product
{

    use ORMBehaviors\SoftDeletable\SoftDeletable;

    const PHOTOS_DIR = 'x/images/products/';

    public function __construct() {
        $this->freePrice = false;
        $this->minimumPrice = 1;
        $this->productsSold = 0;
        $this->validated = false;
        $this->isTicket = false;
        $this->options = new ArrayCollection();
    }

    /*public static function getWebPath(Image $image) {
        return self::PHOTOS_DIR . $image->getFilename();
    }*/

    public static function getWebPath(string $image) {
        return self::PHOTOS_DIR . $image;
    }

    public function __toString()
    {
        return '' . $this->getName();
    }

    public function updateProductsSold($quantity) {
        $this->productsSold += $quantity;
    }

    public function disponibility() {
        return $this->supply - $this->productsSold;
    }

    public function isTicket() {
        return $this->getIsTicket();
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
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToOne(targetEntity="XBundle\Entity\Image", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    //private $photo;

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
     * @var integer
     * 
     * @ORM\Column(name="max_amount_per_purchase", type="smallint")
     */
    private $maxAmountPerPurchase;

    /**
     * @var int
     *
     * @ORM\Column(name="products_sold", type="integer")
     */
    private $productsSold;

    /**
     * @var bool
     * 
     * @ORM\Column(name="is_ticket", type="boolean")
     */
    private $isTicket;

    /**
     * @var bool
     * 
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;

    /**
     * @ORM\OneToMany(targetEntity="XBundle\Entity\OptionProduct", mappedBy="product", cascade={"all"}, orphanRemoval=true)
     */
    private $options;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="x_product_header", fileNameProperty="image")
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $image;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;


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
     * Set supply
     * 
     * @param integer $supply
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
     * Set maxAmountPerPurchase
     * 
     * @param integer $maxAmountPerPurchase;
     * 
     * @return Product
     */
    public function setMaxAmountPerPurchase($maxAmountPerPurchase)
    {
        $this->maxAmountPerPurchase = $maxAmountPerPurchase;

        return $this;
    }

    /**
     * Get maxAmountPerPurchase
     * 
     * @return integer
     */
    public function getMaxAmountPerPurchase()
    {
        return $this->maxAmountPerPurchase;
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

    /**
     * Set validated
     * 
     * @param boolean $validated
     * 
     * @return Product
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
     * Set isTicket
     *
     * @param boolean $isTicket
     *
     * @return Product
     */
    public function setIsTicket($isTicket)
    {
        $this->isTicket = $isTicket;

        return $this;
    }

    /**
     * Get isTicket
     *
     * @return boolean
     */
    public function getIsTicket()
    {
        return $this->isTicket;
    }

    /**
     * Add option
     *
     * @param \XBundle\Entity\OptionProduct $option
     *
     * @return Product
     */
    public function addOption(\XBundle\Entity\OptionProduct $option)
    {
        $option->setProduct($this);
        $this->options[] = $option;
        return $this;
    }

    /**
     * Remove option
     *
     * @param \XBundle\Entity\OptionProduct $option
     */
    public function removeOption(\XBundle\Entity\OptionProduct $option)
    {
        $option->setProduct(null);
        $this->options->removeElement($option);
    }

    /**
     * Get options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
    /**
     * @param mixed $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return Product
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param File $imageFile
     */
    public function setImageFile(File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if ($imageFile){
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }


}
