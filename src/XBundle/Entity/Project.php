<?php

namespace XBundle\Entity;

use AppBundle\Entity\Address;
use AppBundle\Entity\Artist;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use XBundle\Entity\Image;
use XBundle\Entity\Product;
use XBundle\Entity\Tag;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XCategory;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ProjectRepository")
 */
class Project
{
    use ORMBehaviors\Sluggable\Sluggable;

    const PHOTOS_DIR = 'x/images/projects/';

    public function __construct() {
        $this->dateCreation = new \DateTime();
        $this->dateEnd = new \DateTime();
        $this->collectedAmount = 0;
        $this->validated= false;
        $this->deleted = false;
        $this->successful = false;
        $this->failed = false;
        $this->refunded = false;
        $this->noThreshold = true;
        $this->nbDonations = 0;
        $this->nbSales = 0;
        $this->code = uniqid('x');
        $this->projectPhotos = new ArrayCollection();
        $this->handlers = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->points = 0;
        $this->acceptConditions = false;
        $this->contributions= new ArrayCollection();
        $this->notifSent = 0;
    }

    public function getSluggableFields() {
        return ['title'];
    }

    public static function getWebPath(Image $image) {
        return self::PHOTOS_DIR . $image->getFilename();
    }

    public function __toString()
    {
        return '' . $this->getTitle();
    }

    public function hasThreshold()
    {
        return !$this->noThreshold;
    }

    /**
     * Calculates the number of remaining days for project funding
     */
    public function getRemainingDays()
    {
        return $this->getDateEnd()->diff(new \DateTime())->format('%a');
    }

    /**
     * Calculates the number of remaining hours for project funding
     */
    public function getRemainingHours()
    {
        $diff = $this->getDateEnd()->diff(new \DateTime());
        return $diff->h + ($diff->days*24);
    }

    /**
     * Calculates the number of remaining minutes for project funding
     */
    public function getRemainingMinutes()
    {
        $diff = $this->getDateEnd()->diff(new \DateTime());
        return ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    }

    /**
     * Display remaining time
     */
    public function getRemainingTime()
    {
        $time = '';
        if($this->getRemainingDays() > 0) {
            $time .= $this->getRemainingDays() . ' jour(s) restant(s)';
        } else {
            if($this->getRemainingHours() > 0) {
                $time .= $this->getRemainingHours() . ' heure(s) restante(s)';
            } else {
                $time .= $this->getRemainingMinutes() . ' minute(s) restante(s)';
            }
        }
        return $time;
    }

    /**
     * Calculates the percentage of project funding progress
     */
    public function getProgressPercent() {
        return min(floor(($this->getCollectedAmount() / max(1, $this->getThreshold())) * 100), 100);
    }

    public function addAmount($amount) {
        $this->collectedAmount += $amount;
    }

    public function addNbDonations() {
        $this->nbDonations++;
    }

    public function addNbSales() {
        $this->nbSales++;
    }

    // count contributors, once if a contributors paid several times
    public function getNbContributors() {
        $nbContributors = array_unique(array_map(function(XOrder $person) {
            return $person->getEmail();
        }, $this->getContributors()));
        return count($nbContributors);
    }

    public function isPassed() {
        return $this->dateEnd < new \DateTime();
    }

    public function isPending() {
        return !$this->successful && !$this->failed && !$this->refunded;
    }

    public function isEvent() {
        return $this->dateEvent != null;
    }

    // Get donations paid
    private $donationsPaid = null;
    public function getDonationsPaid() {
        if($this->donationsPaid == null) {
            $this->donationsPaid = array_filter($this->contributions->toArray(), function(XContractFan $contribution) {
                return $contribution->getIsDonation() && $contribution->getPaid() && ($this->failed || !$contribution->getRefunded());
            });
        }
        return $this->donationsPaid;
    }

    // Get all donators
    public function getDonators() {
        if($this->getDonationsPaid() == null || empty($this->getDonationsPaid())) {
            return [];
        }
        return array_map(function(XContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getDonationsPaid());
    }

    // Get sales paid
    private $salesPaid = null;
    public function getSalesPaid() {
        if($this->salesPaid == null) {
            $this->salesPaid = array_filter($this->contributions->toArray(), function(XContractFan $contribution) {
                return !$contribution->getIsDonation() && $contribution->getPaid() && ($this->failed || !$contribution->getRefunded());
            });
        }
        return $this->salesPaid;
    }

    // Get all buyers
    public function getBuyers() {
        if($this->getSalesPaid() == null || empty($this->getSalesPaid())) {
            return [];
        }
        return array_map(function(XContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getSalesPaid());
    }

    // Get all contributions paid
    private $contributionsPaid = null;
    public function getContributionsPaid() {
        if($this->contributionsPaid == null) {
            $this->contributionsPaid = array_filter($this->contributions->toArray(), function(XContractFan $contribution) {
                return $contribution->getPaid() && ($this->failed || !$contribution->getRefunded());
            });
        }
        return $this->contributionsPaid;
    }

    // Get all contributors
    public function getContributors() {
        if($this->getContributionsPaid() == null || empty($this->getContributionsPaid())) {
            return [];
        }
        return array_map(function(XContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getContributionsPaid());
    }


    // To get only artists that creator owns (form only)
    private $creator;

    // Conditions approval (form only)
    private $acceptConditions;


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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Artist")
     * @ORM\JoinColumn(nullable=false)
     */
    private $artist;

    /**
     * @ORM\ManyToMany(targetEntity="\AppBundle\Entity\User", inversedBy="projects")
     */
    private $handlers;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="motivations", type="text", nullable=true)
     */
    private $motivations;

    /**
     * @var string
     *
     * @ORM\Column(name="threshold_purpose", type="text", nullable=true)
     */
    private $thresholdPurpose;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime")
     */
    private $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XCategory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @var float
     *
     * @ORM\Column(name="threshold", type="float", nullable=true)
     */
    private $threshold;

    /**
     * @var float
     *
     * @ORM\Column(name="collected_amount", type="float")
     */
    private $collectedAmount;

    /**
     * @var bool
     * 
     * @ORM\Column(name="validated", type="boolean")
     */
    private $validated;

    /**
     * @var bool
     * 
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @var bool
     *
     * @ORM\Column(name="successful", type="boolean")
     */
    private $successful;

    /**
     * @var bool
     *
     * @ORM\Column(name="failed", type="boolean")
     */
    private $failed;

    /**
     * @var bool
     *
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @var bool
     *
     * @ORM\Column(name="no_threshold", type="boolean")
     */
    private $noThreshold;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_donations", type="integer")
     */
    private $nbDonations;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_sales", type="integer")
     */
    private $nbSales;

    /**
     * @ORM\OneToOne(targetEntity="\XBundle\Entity\Image", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $coverpic;

    /**
     * @ORM\ManyToMany(targetEntity="\XBundle\Entity\Image", cascade={"all"})
     */
    private $projectPhotos;

    /**
     * @ORM\OneToMany(targetEntity="\XBundle\Entity\Product", mappedBy="project", cascade={"all"})
     */
    private $products;

    /**
     * @ORM\ManyToMany(targetEntity="\XBundle\Entity\Tag", cascade={"all"})
     */
    private $tags;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var int
     * 
     * @ORM\Column(name="points", type="integer")
     */
    private $points;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="XBundle\Entity\XContractFan", mappedBy="project", cascade={"persist"})
     */
    private $contributions;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="notif_sent", type="boolean")
     */
    private $notifSent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_event", type="datetime", nullable=true)
     */
    private $dateEvent;

    /**
     * @var Address
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;



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
     * Set title
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set artist
     *
     * @param Artist $artist
     *
     * @return Project
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Project
     */
    /*public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }*/

    /**
     * Get user
     *
     * @return User
     */
    /*public function getUser()
    {
        return $this->user;
    }*/


    /**
     * Add handler
     *
     * @param $handler
     *
     * @return Project
     */
    public function addHandler($handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Remove handler
     *
     * @param User $handler
     */
    public function removeHandler($handler)
    {
        $this->handlers->removeElement($handler);
    }

    /**
     * Get handlers
     *
     * @return Collection
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
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
     * Set motivations
     *
     * @param string $motivations
     *
     * @return Project
     */
    public function setMotivations($motivations)
    {
        $this->motivations = $motivations;

        return $this;
    }

    /**
     * Get motivations
     *
     * @return string
     */
    public function getMotivations()
    {
        return $this->motivations;
    }

    /**
     * Set thresholdPurpose
     *
     * @param string $thresholdPurpose
     *
     * @return Project
     */
    public function setThresholdPurpose($thresholdPurpose)
    {
        $this->thresholdPurpose = $thresholdPurpose;

        return $this;
    }

    /**
     * Get thresholdPurpose
     *
     * @return string
     */
    public function getThresholdPurpose()
    {
        return $this->thresholdPurpose;
    }


    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Project
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Project
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set category
     *
     * @param XCategory $category
     *
     * @return Project
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return XCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set threshold
     *
     * @param float $threshold
     *
     * @return Project
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get threshold
     *
     * @return float
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set collectedAmount
     *
     * @param float $collectedAmount
     *
     * @return Project
     */
    public function setCollectedAmount($collectedAmount)
    {
        $this->collectedAmount = $collectedAmount;

        return $this;
    }

    /**
     * Get collectedAmount
     *
     * @return float
     */
    public function getCollectedAmount()
    {
        return $this->collectedAmount;
    }

    /**
     * Set validated
     * 
     * @param boolean $validated
     * 
     * @return Project
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
     * Set deleted
     * 
     * @param boolean $deleted
     * 
     * @return Project
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     * 
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set successful
     *
     * @param boolean $successful
     *
     * @return Project
     */
    public function setSuccessful($successful)
    {
        $this->successful = $successful;

        return $this;
    }

    /**
     * Get successful
     *
     * @return bool
     */
    public function getSuccessful()
    {
        return $this->successful;
    }

    /**
     * Set failed
     *
     * @param boolean $failed
     *
     * @return Project
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed
     *
     * @return bool
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return Project
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return bool
     */
    public function getRefunded()
    {
        return $this->refunded;
    }

    /**
     * Set noThreshold
     *
     * @param boolean $noThreshold
     *
     * @return Project
     */
    public function setNoThreshold($noThreshold)
    {
        $this->noThreshold = $noThreshold;

        return $this;
    }

    /**
     * Get noThreshold
     *
     * @return bool
     */
    public function getNoThreshold()
    {
        return $this->noThreshold;
    }

    /**
     * Set nbDonations
     *
     * @param integer $nbDonations
     *
     * @return Project
     */
    public function setNbDonations($nbDonations)
    {
        $this->nbDonations = $nbDonations;

        return $this;
    }

    /**
     * Get nbDonations
     *
     * @return int
     */
    public function getNbDonations()
    {
        return $this->nbDonations;
    }

    /**
     * Set nbSales
     *
     * @param integer $nbSales
     *
     * @return Project
     */
    public function setNbSales($nbSales)
    {
        $this->nbSales = $nbSales;

        return $this;
    }

    /**
     * Get nbSales
     *
     * @return int
     */
    public function getNbSales()
    {
        return $this->nbSales;
    }

    /**
     * Set coverpic
     *
     * @param Image $coverpic
     *
     * @return Project
     */
    public function setCoverpic($coverpic = null)
    {
        $this->coverpic = $coverpic;

        return $this;
    }

    /**
     * Get coverpic
     *
     * @return Image
     */
    public function getCoverpic()
    {
        return $this->coverpic;
    }


    /**
     * Add projectPhoto
     *
     * @param Image $projectPhoto
     *
     * @return Project
     */
    public function addProjectPhoto($projectPhoto)
    {
        $this->projectPhotos[] = $projectPhoto;

        return $this;
    }

    /**
     * Remove projectPhoto
     *
     * @param Image $projectPhoto
     */
    public function removeProjectPhoto(Image $projectPhoto)
    {
        $this->projectPhotos->removeElement($projectPhoto);
    }

    /**
     * Get projectPhotos
     *
     * @return Collection
     */
    public function getProjectPhotos()
    {
        return $this->projectPhotos;
    }

    /**
     * Add product
     * 
     * @param Product $product
     * 
     * @return Project
     */
    public function addProduct($product)
    {
        $this->products[] = $product;
        $product->setProject($this);
    }

    /**
     * Remove product
     * 
     * @param Product $product
     */
    public function removeProduct($product)
    {
        $this->products->removeElement($product);
        $product->setProject(null);
        return $this;
    }

    /**
     * Get products
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Project
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set points
     * 
     * @return Project
     */
    public function setPoints($points)
    {
        $this->points = $points;
        
        return $this;
    }

    /**
     * Get points
     * 
     * @return int
     */
    public function getPoints(){
        return $this->points;
    }

    
    // Form only
    /**
     * Set creator
     *
     * @param User $creator
     *
     * @return Project
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param boolean $acceptConditions
     */
    public function setAcceptConditions($acceptConditions)
    {
        $this->acceptConditions = $acceptConditions;
    }

    /**
     * @return boolean
     */
    public function getAcceptConditions()
    {
        return $this->acceptConditions;
    }

    


    /**
     * Add tag
     *
     * @param \XBundle\Entity\Tag $tag
     *
     * @return Project
     */
    public function addTag(\XBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \XBundle\Entity\Tag $tag
     */
    public function removeTag(\XBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add contribution
     *
     * @param \XBundle\Entity\XContractFan $contribution
     *
     * @return Project
     */
    public function addContribution(\XBundle\Entity\XContractFan $contribution)
    {
        $this->contributions[] = $contribution;

        return $this;
    }

    /**
     * Remove contribution
     *
     * @param \XBundle\Entity\XContractFan $contribution
     */
    public function removeContribution(\XBundle\Entity\XContractFan $contribution)
    {
        $this->contributions->removeElement($contribution);
    }

    /**
     * Get contributions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContributions()
    {
        return $this->contributions;
    }

    /**
     * Set notifSent
     *
     * @param boolean $notifSent
     *
     * @return Project
     */
    public function setNotifSent($notifSent)
    {
        $this->notifSent = $notifSent;

        return $this;
    }

    /**
     * Get notifSent
     *
     * @return boolean
     */
    public function getNotifSent()
    {
        return $this->notifSent;
    }

    /**
     * Set dateEvent
     *
     * @param \DateTime $dateEvent
     *
     * @return Project
     */
    public function setDateEvent($dateEvent)
    {
        $this->dateEvent = $dateEvent;

        return $this;
    }

    /**
     * Get dateEvent
     *
     * @return \DateTime
     */
    public function getDateEvent()
    {
        return $this->dateEvent;
    }

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return Project
     */
    public function setAddress(\AppBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
