<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LineUpRepository")
 * @ORM\Table(name="lineup")
 **/
class LineUp
{
    public function __construct()
    {
        $this->performances = new ArrayCollection();
    }

    public function __toString()
    {
        if($this->festivalDay == null || $this->stage == null) {
            return 'Nouvelle lineup';
        }
        return $this->getFestivalDay()->__toString() . ' (lineup : '.$this->stage->__toString();
    }

    public static function sortPerformancesAsc($performances) {
        if( count( $performances) < 2 ) {
            return $performances;
        }
        $left = $right = array( );
        reset( $performances);

        $pivot_key  = key( $performances );
        $pivot  = array_shift( $performances );

        foreach( $performances as $k => $v ) {
            if($pivot->getTime() == null || $v->getTime() < $pivot->getTime() )
                $left[$k] = $v;
            else
                $right[$k] = $v;
        }
        return array_merge(self::sortPerformancesAsc($left), array($pivot_key => $pivot), self::sortPerformancesAsc($right));
    }


    public function getPerformancesAsc() {
        $performances = $this->performances->toArray();
        return self::sortPerformancesAsc($performances);
    }

    public function getFestival() {
        if($this->festivalDay == null) {
            return null;
        }
        return $this->festivalDay->getFestival();
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
     * @ORM\ManyToOne(targetEntity="FestivalDay", inversedBy="lineups")
     * @var FestivalDay
     */
    protected $festivalDay;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ArtistPerformance", mappedBy="lineup")
     */
    private $performances;

    /**
     * @var Stage
     * @ORM\ManyToOne(targetEntity="Stage", inversedBy="lineups")
     */
    private $stage;

    /**
     * Add performance
     *
     * @param \AppBundle\Entity\ArtistPerformance $performance
     *
     * @return LineUp
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

    public function getFestivalDay() {
        return $this->festivalDay;
    }
    public function setFestivalDay($festivalDay) {
        $this->festivalDay = $festivalDay;
        return $this;
    }
    public function getStage() {
        return $this->stage;
    }
    public function setStage($stage) {
        $this->stage = $stage;
        return $this;
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
}