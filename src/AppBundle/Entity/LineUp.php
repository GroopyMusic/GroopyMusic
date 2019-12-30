<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LineUpRepository")
 * @ORM\Table(name="lineup")
 **/
class LineUp implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __construct()
    {
        $this->performances = new ArrayCollection();
        $this->ticketsSold = 0;
        $this->ticketsSoldPostVal = 0;
        $this->successful = 0;
        $this->failed = 0;
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch(\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function getDefaultLocale() {
        return 'fr';
    }

    public function __toString()
    {
        if($this->festivalDay == null || $this->stage == null) {
            return 'Nouvelle lineup';
        }
        return $this->getFestivalDay()->__toString() . ' (lineup ' .  $this->getName() . ' @'.$this->stage->__toString();
    }

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
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

    public function getArtists() {
        return array_map(function (ArtistPerformance $ap) {
            return $ap->getArtist();
        }, $this->performances->toArray());
    }

    public function addTicketsSold($quantity) {
        $this->ticketsSold += $quantity;
    }
    public function addTicketsSoldPostVal($quantity) {
        $this->ticketsSoldPostVal += $quantity;
    }

    public function getPercentageObjective() {
        if($this->threshold == 0) {
            return 0;
        }
        return round(($this->ticketsSold / $this->threshold) * 100, 0);
    }

    public function isSoldOut() {
        return $this->soldout_amount <= $this->ticketsSold;
    }

    public function getNbAvailable() {
        return $this->soldout_amount - $this->ticketsSold;
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
     * @ORM\JoinColumn(nullable=true)
     */
    private $stage;

    /**
     * @ORM\Column(name="tickets_sold", type="float")
     */
    private $ticketsSold;

    /**
     * @ORM\Column(name="tickets_sold_post_val", type="float")
     */
    private $ticketsSoldPostVal;

    /**
     * @ORM\Column(name="threshold", type="float")
     */
    private $threshold;

    /**
     * @ORM\Column(name="soldout_amount", type="float")
     */
    private $soldout_amount;

    /**
     * @ORM\Column(name="successful", type="boolean")
     */
    private $successful;

    /**
     * @ORM\Column(name="failed", type="boolean")
     */
    private $failed;

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

    public function setTicketsSold($quantity) {
        $this->ticketsSold = $quantity;
        return $this;
    }
    public function getTicketsSold() {
        return $this->ticketsSold;
    }

    public function setThreshold($threshold) {
        $this->threshold = $threshold;
        return $this;
    }
    public function getThreshold() {
        return $this->threshold;
    }
    public function setSoldoutAmount($soldout_amount) {
        $this->soldout_amount = $soldout_amount;
        return $this;
    }
    public function getSoldoutAmount() {
        return $this->soldout_amount;
    }

    public function setSuccessful($successful) {
        $this->successful = $successful;
        return $this;
    }
    public function getSuccessful() {
        return $this->successful;
    }
    public function isSuccessful() {
        return $this->getSuccessful();
    }
    public function setFailed($failed) {
        $this->failed = $failed;
        return $this;
    }
    public function getFailed() {
        return $this->failed;
    }
    public function isFailed() {
        return $this->getFailed();
    }

    /**
     * @return mixed
     */
    public function getTicketsSoldPostVal()
    {
        return $this->ticketsSoldPostVal;
    }

    /**
     * @param mixed $ticketsSoldPostVal
     */
    public function setTicketsSoldPostVal($ticketsSoldPostVal)
    {
        $this->ticketsSoldPostVal = $ticketsSoldPostVal;
        return $this;
    }
}