<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Genre;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PropositionArtist
 *
 * @ORM\Table(name="proposition_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PropositionArtistRepository")
 */
class PropositionArtist
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->date_creation = new \DateTime();
        $this->deleted = false;
    }

    public function __toString()
    {
        return $this->artistname;
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
     * @ORM\Column(name="artistname", type="string", length=67)
     */
    private $artistname;

    /**
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $date_creation;

    /**
     * @var string
     * @ORM\Column(name="demo_link", type="string", length=255, nullable=true)
     */
    private $demo_link;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;


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
     * Set artistname
     *
     * @param string $artistname
     *
     * @return PropositionArtist
     */
    public function setArtistname($artistname)
    {
        $this->artistname = $artistname;

        return $this;
    }

    /**
     * Get artistname
     *
     * @return string
     */
    public function getArtistname()
    {
        return $this->artistname;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     * @return PropositionArtist
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return PropositionArtist
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set demoLink
     *
     * @param string $demoLink
     *
     * @return PropositionArtist
     */
    public function setDemoLink($demoLink)
    {
        $this->demo_link = $demoLink;

        return $this;
    }

    /**
     * Get demoLink
     *
     * @return string
     */
    public function getDemoLink()
    {
        return $this->demo_link;
    }

    /**
     * Add genre
     *
     * @param Genre $genre
     *
     * @return PropositionArtist
     */
    public function addGenre(Genre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    /**
     * Remove genre
     *
     * @param Genre $genre
     */
    public function removeGenre(Genre $genre)
    {
        $this->genres->removeElement($genre);
    }

    /**
     * Get genres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGenres()
    {
        return $this->genres;
    }
}
