<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use XBundel\Entity\Points; 

/**
 * Projects
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ProjectsRepository")
 * @Vich\Uploadable
 */
class Projects
{
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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Video", cascade={"persist"})
     */
    private $video;

    /**
     * @ORM\OneToOne(targetEntity="\XBundle\Entity\Image", cascade={"persist"})
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Artist")
     * @ORM\JoinColumn(nullable=true)
     */
    private $artist;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;
	

	/**
     * @var float
     *
     * @ORM\Column(name="fund", type="float")
     */
    private $fund;

    /**
     * @var float
     *
     * @ORM\Column(name="totalAmount", type="float", nullable = true)
     */
    private $totalAmount;

    /**
    * @var int
    * @ORM\Column(name="points", type="integer")
    */
    private $points;

    /**
     * @ORM\ManyToMany(targetEntity="XBundle\Entity\Tag", cascade={"persist"})
     *
    */
    private $tags;




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
     * @return Projects
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
     * @return Projects
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
     * Set photo
     *
     * @param string $photo
     *
     * @return Projects
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

	
	/**
     * Set fund
     *
     * @param float $fund
     *
     * @return Projects
     */
    public function setFund($fund)
    {
        $this->fund = $fund;

        return $this;
    }

    /**
     * Get fund
     *
     * @return float
     */
    public function getFund()
    {
        return $this->fund;
    }

    public function setPoints($points)
    {
        $this->points = $points;

    }

    public function getPoints(){
        return $this->points;
    }
	
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->video = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tag
     *
     * @param \XBundle\Entity\Tag $tag
     *
     * @return Projects
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
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return Projects
     */
    public function setArtist(\AppBundle\Entity\Artist $artist = null)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * Get artist
     *
     * @return \AppBundle\Entity\Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Projects
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set totalAmount
     *
     * @param float $totalAmount
     *
     * @return Projects
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Get totalAmount
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }


    /**
     * Add video
     *
     * @param \AppBundle\Entity\Video $video
     *
     * @return Projects
     */
    public function addVideo(\AppBundle\Entity\Video $video)
    {
        $this->video[] = $video;

        return $this;
    }

    /**
     * Remove video
     *
     * @param \AppBundle\Entity\Video $video
     */
    public function removeVideo(\AppBundle\Entity\Video $video)
    {
        $this->video->removeElement($video);
    }

    /**
     * Get video
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getVideo()
    {
        return $this->video;
    }
}
