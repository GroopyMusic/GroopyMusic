<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 05-02-17
 * Time: 18:46
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PUGX\MultiUserBundle\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="artist")
 */
class Artist
{
    public function __construct()
    {
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="artistname", type="string", length=67)
     */
    private $artistname;

    /**
     * @ORM\ManyToOne(targetEntity="Phase")
     * @ORM\JoinColumn(nullable=false)
     */
    private $phase;

    /**
     * @ORM\ManyToMany(targetEntity="Genre")
     */
    private $genres;

    /**
     * Set artistname
     *
     * @param string $artistname
     *
     * @return UserArtist
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
     * Set phase
     *
     * @param \AppBundle\Entity\Phase $phase
     *
     * @return UserArtist
     */
    public function setPhase(\AppBundle\Entity\Phase $phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return \AppBundle\Entity\Phase
     */
    public function getPhase()
    {
        return $this->phase;
    }

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
     * Add genre
     *
     * @param \AppBundle\Entity\Genre $genre
     *
     * @return Artist
     */
    public function addGenre(\AppBundle\Entity\Genre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    /**
     * Remove genre
     *
     * @param \AppBundle\Entity\Genre $genre
     */
    public function removeGenre(\AppBundle\Entity\Genre $genre)
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
