<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FestivalDayRepository")
 * @ORM\Table(name="festivalday")
 **/
class FestivalDay
{
    public function __construct()
    {
        $this->performances = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getArtistPerformances() {
        return $this->getPerformances();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime $date
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var Hall
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Hall")
     * @ORM\JoinColumn(nullable=true)
     */
    private $hall;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArtistPerformance", mappedBy="festivalday")
     */
    private $performances;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\CounterPart", mappedBy="festivaldays")
     */
    private $counterparts;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FestivalDay
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return FestivalDay
     */
    public function setHall(\AppBundle\Entity\Hall $hall = null)
    {
        $this->hall = $hall;

        return $this;
    }

    /**
     * Get hall
     *
     * @return \AppBundle\Entity\Hall
     */
    public function getHall()
    {
        return $this->hall;
    }

    /**
     * Add performance
     *
     * @param \AppBundle\Entity\ArtistPerformance $performance
     *
     * @return FestivalDay
     */
    public function addPerformance(\AppBundle\Entity\ArtistPerformance $performance)
    {
        $this->performances[] = $performance;

        return $this;
    }

    /**
     * Remove performance
     *
     * @param \AppBundle\Entity\ArtistPerformance $performance
     */
    public function removePerformance(\AppBundle\Entity\ArtistPerformance $performance)
    {
        $this->performances->removeElement($performance);
    }

    /**
     * Get performances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerformances()
    {
        return $this->performances;
    }

    /**
     * Add counterpart
     *
     * @param \AppBundle\Entity\CounterPart $counterpart
     *
     * @return FestivalDay
     */
    public function addCounterpart(\AppBundle\Entity\CounterPart $counterpart)
    {
        $this->counterparts[] = $counterpart;

        return $this;
    }

    /**
     * Remove counterpart
     *
     * @param \AppBundle\Entity\CounterPart $counterpart
     */
    public function removeCounterpart(\AppBundle\Entity\CounterPart $counterpart)
    {
        $this->counterparts->removeElement($counterpart);
    }

    /**
     * Get counterparts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounterparts()
    {
        return $this->counterparts;
    }
}
