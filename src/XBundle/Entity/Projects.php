<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

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
     * @ORM\OneToOne(targetEntity="\XBundle\Entity\Image", cascade={"persist"})
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="artist_name", type="string", length=255)
     */
    private $artistName;
	
	/**
     * @var float
     *
     * @ORM\Column(name="fund", type="float")
     */
    private $fund;


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
     * Set artistName
     *
     * @param string $artistName
     *
     * @return Projects
     */
    public function setArtistName($artistName)
    {
        $this->artistName = $artistName;

        return $this;
    }

    /**
     * Get artistName
     *
     * @return string
     */
    public function getArtistName()
    {
        return $this->artistName;
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
	
}
