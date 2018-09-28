<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArtistPerformanceRepository")
 * @ORM\Table(name="artistperformance")
 **/
class ArtistPerformance
{
    public function __toString() {
        if($this->artist == null) {
            return 'Nouvelle performance';
        }

        $str = $this->artist->getArtistName();

        if($this->time != null) {
            $str .=  ' à ' . $this->time->format('H:i');
        }

        return $str;
    }

    public function getTimeEnd() {
        if($this->time != null) {
            $time2 = clone $this->time;
            $time2->modify('+'.$this->duration.'minutes');
            return $time2;
        }
        return $this->time;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Artist
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="performances")
     */
    private $artist;

    /**
     * @var FestivalDay
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FestivalDay", inversedBy="performances")
     */
    private $festivalday;

    /**
     * @var \DateTime
     * @ORM\Column(name="time", type="time", nullable=true)
     */
    private $time;

    /**
     * @var int
     * @ORM\Column(name="duration", type="smallint", nullable=true)
     */
    private $duration;

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
     * Set time
     *
     * @param \DateTime $time
     *
     * @return ArtistPerformance
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return ArtistPerformance
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return ArtistPerformance
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
     * Set festivalday
     *
     * @param \AppBundle\Entity\FestivalDay $festivalday
     *
     * @return ArtistPerformance
     */
    public function setFestivalday(\AppBundle\Entity\FestivalDay $festivalday = null)
    {
        $this->festivalday = $festivalday;

        return $this;
    }

    /**
     * Get festivalday
     *
     * @return \AppBundle\Entity\FestivalDay
     */
    public function getFestivalday()
    {
        return $this->festivalday;
    }
}
