<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Reward;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ContractArtist
 *
 * @ORM\Table(name="contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractArtistRepository")
 */
class ContractArtist extends BaseContractArtist
{
    const NB_DAYS_OF_CLOSING = 0;
    const FESTIVAL_START_HOUR = 18;
    const FESTIVAL_START_MINUTE = 0;

    const MAXIMUM_PROMO_OVERFLOW = 5;

    const STATE_REFUNDED = 'state.refunded';
    const STATE_FAILED = 'state.failed';
    const STATE_SUCCESS_SOLDOUT = 'state.success.soldout.soldout';
    const STATE_SUCCESS_SOLDOUT_PENDING = 'state.success.soldout.pending';
    const STATE_SUCCESS_CLOSED = 'state.success.closed';
    const STATE_SUCCESS_ONGOING = 'state.success.ongoing';
    const STATE_SUCCESS_PENDING = 'state.success.pending';
    const STATE_SUCCESS_PASSED = 'state.success.passed';
    const STATE_ONGOING = 'state.ongoing';
    const STATE_PENDING = 'state.pending';
    const STATE_TEST_PERIOD = 'state.test_period';

    public function __construct()
    {
        parent::_construct();
        $this->tickets_sold = 0;
        $this->tickets_reserved = 0;
        $this->tickets_sent = false;
        $this->nb_closing_days = self::NB_DAYS_OF_CLOSING;
        $this->min_tickets = 0;
        $this->festivaldays = new ArrayCollection();
    }

    public function isUncrowdable() {
        return in_array($this->getState(), $this->getUncrowdableStates());
    }

    public function isSoldOut() {
        return in_array($this->getState(), $this->getSoldOutStates());
    }

    public function isCrowdable() {
        return !$this->isUncrowdable();
    }

    public function isPending() {
        return in_array($this->getState(), $this->getPendingStates());
    }

    public function getDisplayName() {
        if($this->artist == null) {
            $str = $this->step;
            foreach($this->getAllArtists() as $artist) {
                $str .= ' - ' . $artist;
            }
            return $str;
        }
        else {
            return $this->step . ' de ' . $this->artist;
        }
    }

    public function getTitleWithDates() {
        $str = $this->getTitle();
        if(!empty($this->getFestivalDates())) {
            $str .= ' (';
            $i = 1;
            foreach($this->getFestivalDates() as $date) {
                if($i > 1)
                    $str .= ' - ';
                $str .= $date->format('d/m/Y');
                $i++;
            }
            $str.= ')';
        }
        return $str;
    }

    public static function getUncrowdableStates() {
        return [
            self::STATE_REFUNDED,
            self::STATE_FAILED,
            self::STATE_SUCCESS_SOLDOUT,
            self::STATE_SUCCESS_SOLDOUT_PENDING,
            self::STATE_SUCCESS_CLOSED,
            self::STATE_SUCCESS_PASSED,
            self::STATE_PENDING,
        ];
    }

    public static function getPendingStates() {
        return [
            self::STATE_PENDING,
            self::STATE_SUCCESS_PENDING,
            self::STATE_SUCCESS_SOLDOUT_PENDING,
        ];
    }

    public static function getSuccessfulStates() {
        return [
            self::STATE_SUCCESS_PENDING,
            self::STATE_SUCCESS_SOLDOUT,
            self::STATE_SUCCESS_SOLDOUT_PENDING,
            self::STATE_SUCCESS_CLOSED,
            self::STATE_SUCCESS_PASSED,
            self::STATE_SUCCESS_ONGOING,
        ];
    }

    public static function getPassedStates() {
        return [
            self::STATE_SUCCESS_PASSED,
            self::STATE_FAILED,
            self::STATE_REFUNDED,
        ];
    }

    public static function getSoldOutStates() {
        return [
            self::STATE_SUCCESS_SOLDOUT,
            self::STATE_SUCCESS_SOLDOUT_PENDING,
        ];
    }

    public function isInSuccessfulState() {
        return in_array($this->getState(), self::getSuccessfulStates());
    }

    public function isPassed() {
        return in_array($this->getState(), self::getPassedStates());
    }

    public function getPercentObjective() {
       return floor(($this->getTotalBookedTickets() / $this->getMinTickets()) * 100);
    }

    public function getTotalBookedTickets() {
        return $this->tickets_reserved + $this->tickets_sold;
    }

    public function getTotalBookedTicketsMajored() {
        return min($this->getTotalBookedTickets(), $this->getMaxTickets());
    }

    public function getLastSellingDate() {
        $dateconcert_copy = clone $this->getLastFestivalDate();
        return $dateconcert_copy->modify('-' . ($this->nb_closing_days) . ' days');
    }

    public function isLastSellingDate() {
        return (new \DateTime())->diff($this->getLastSellingDate())->days == 0 && (new \DateTime() >= $this->getLastSellingDate());
    }

    public function isDeadlineDate() {
        $today = new \DateTime();
        return $today->diff($this->dateEnd)->days == 1 && $today < $this->dateEnd;
    }

    public function getTicketsSoldMajored() {
        return min($this->getTicketsSold(), $this->getMaxTickets());
    }

    public function getMaxTickets() {
        return array_sum(array_map(function(CounterPart $counterPart) {
            return $counterPart->getMaximumAmount();
        }, $this->getCounterParts()->toArray()));
    }

    public function getTotalNbAvailable() {
        return $this->getMaxTickets() - $this->getTotalBookedTickets();
    }

    public function isValidatedBelowObjective() {
        return $this->isInSuccessfulState() && $this->getTicketsSold() < $this->getMinTickets();
    }

    public function getMinTickets() {
        if($this->min_tickets <= 0) {
            return $this->getStep()->getMinTickets();
        }
        else {
            return $this->min_tickets;
        }
    }

    public function getNbTicketsToSuccess() {
        $min = $this->getMinTickets();
        $booked = $this->getTotalBookedTickets();
        if($booked >= $min)
            return 0;

        return $min - $booked;
    }

    public function getState() {

        if(isset($this->state)) {
            return $this->state;
        }

        $today = new \DateTime();
        $today2 = new \DateTime();
        $today3 = new \DateTime();

        $max_tickets = $this->getMaxTickets();

        // Failure & refunded
        if($this->refunded)
            return self::STATE_REFUNDED;

        // Marked as failure
        if($this->failed)
            return self::STATE_FAILED;

        // Marked as success
        if($this->successful)
        {
            // Concert in the future
            if($this->getLastFestivalDate() >= $today3->modify('-1 day')) {
                // Sold out
                if ($this->getTotalBookedTickets() >= $max_tickets)
                    return self::STATE_SUCCESS_SOLDOUT;
                // No more selling
                $dateConcert2 = clone $this->getLastFestivalDate();
                if ($today2->modify('+' . ($this->nb_closing_days) . ' days') > $dateConcert2)
                    return self::STATE_SUCCESS_CLOSED;
                // Successful, in the future, not sold out, not closed => ongoing
                else
                    return self::STATE_SUCCESS_ONGOING;
            }
            // Concert in the passed & successful
            else
                return self::STATE_SUCCESS_PASSED;
        }

        // Crowdfunding is not over yet
        if($this->dateEnd->diff($today)->days > 0) {
            // But already sold out
            if ($this->getTotalBookedTickets() >= $max_tickets)
                return self::STATE_SUCCESS_SOLDOUT_PENDING;

            // Or already successful but not sold out and with a need of validation
            if ($this->getTotalBookedTickets() >= $this->getMinTickets())
                return self::STATE_SUCCESS_PENDING;

            // Or in pre-validaton
            if($this->isInTestPeriod()) {
                return self::STATE_TEST_PERIOD;
            }

            // Or simply ongoing
            return self::STATE_ONGOING;
        }

        // Crowdfunding is over but not marked as successful nor failed -> need for admin validation
        return self::STATE_PENDING;
    }

    public function setArtist(\AppBundle\Entity\Artist $artist)
    {
        parent::setArtist($artist);
        $this->main_artist = $artist;
        return $this;
    }

    public function addTicketsSold($quantity) {
        $this->tickets_sold += $quantity;
    }

    public function removeTicketsSold($quantity) {
        $this->tickets_sold -= $quantity;
    }

    public function addTicketsReserved($quantity) {
        $this->tickets_reserved += $quantity;
    }

    public function removeTicketsReserved($quantity) {
        $this->tickets_reserved -= $quantity;
    }

    private $festivalDates = null;
    private $festivalHalls = null;

    /** @return array */
    public function getFestivalDates() {
        if($this->festivalDates == null) {
            $this->festivalDates = array_map(function (FestivalDay $festivalDay) {
                return $festivalDay->getDate();
            }, $this->festivaldays->toArray());
        }
        return $this->festivalDates;
    }

    /** @return array */
    public function getFestivalHalls() {
        if($this->festivalHalls == null) {
            $this->festivalHalls = array_map(function (FestivalDay $festivalDay) {
                return $festivalDay->getHall();
            }, $this->festivaldays->toArray());
        }
        return $this->festivalHalls;
    }

    /** @return bool */
    public function hasOnlyOneDate() {
        return count($this->getFestivalDates()) == 1;
    }

    /** @return \DateTime */
    public function getOnlyDate() {
        return $this->getFestivalDates()[0];
    }

    public function getFirstFestivalDate() {
        return min($this->getFestivalDates());
    }

    public function getLastFestivalDate() {
        return max($this->getFestivalDates());
    }

    /** @return bool */
    public function hasOnlyOneHall() {
        return count(array_filter($this->getFestivalHalls(), function($elem) { return $elem != null; })) == 1;
    }

    /** @return Hall */
    public function getOnlyHall() {
        return array_filter($this->getFestivalHalls(), function($elem) { return $elem != null; })[0];
    }

    /** @return bool */
    public function hasNoDefinedHall() {
        return count(array_filter($this->getFestivalHalls(), function($elem) { return $elem != null; })) == 0;
    }

    public function getFirstHall() {
        return $this->getOnlyHall();
    }

    public function getCounterPartsSent() {
        return $this->getTicketsSent();
    }

    public function getNbPayments() {
        return count(array_filter($this->payments->toArray(), function($elem) {
            return !$elem->getRefunded();
        }));
    }

    // unmapped, memoized
    private $artistperformances = null;

    public function getArtistPerformances() {
        if($this->artistperformances == null) {
            $performances_days = [];
            $i = 0;

            foreach($this->getFestivaldays() as $festivalDay) {
                foreach($festivalDay->getArtistPerformances() as $artistPerformance) {
                    $performances_days[$i][] = $artistPerformance;
                }
                $i++;
            }

            $this->artistperformances = $performances_days;
        }
        return $this->artistperformances;
    }

    // unmapped, memoized
    private $all_artists = null;

    public function getAllArtists()
    {
        if($this->all_artists == null) {
            $all_artists = [];
            foreach($this->getArtistPerformances() as $artistPerformance_day) {
                foreach($artistPerformance_day as $artistPerformance)
                    $all_artists[] = $artistPerformance->getArtist();
            }
            $this->all_artists = $all_artists;
        }

        return $this->all_artists;
    }

    // Unmapped, memoized
    private $genres;

    public function getGenres() {
        if($this->genres != null) {
            return $this->genres;
        }

        $genres = [];
        foreach($this->getAllArtists() as $artist) {
            foreach($artist->getGenres() as $genre)
                $genres[] = $genre;
        }
        $this->genres = array_unique($genres);
        return $this->genres;
    }

    /**
     * @var Step
     *
     * @ORM\ManyToOne(targetEntity="Step")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $step;

    /**
     * @ORM\Column(name="tickets_sold", type="smallint")
     */
    private $tickets_sold;

    /**
     * @ORM\Column(name="tickets_sent", type="boolean")
     */
    private $tickets_sent;

    /**
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="contracts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $main_artist;

    /**
     * @ORM\Column(name="nb_closing_days", type="smallint")
     */
    private $nb_closing_days;

    /**
     * @ORM\Column(name="min_tickets", type="smallint")
     */
    private $min_tickets;

    /**
     * @ORM\Column(name="tickets_reserved", type="smallint")
     */
    private $tickets_reserved;

    /**
     * @ORM\ManyToOne(targetEntity="Reward", inversedBy="contract_artists_sponsorships")
     */
    private $sponsorship_reward;

    /**
     * @var bool
     * @ORM\Column(name="known_lineup", type="boolean")
     */
    private $known_lineup;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\FestivalDay")
     */
    private $festivaldays;

    /**
     * Set step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return ContractArtist
     */
    public function setStep(\AppBundle\Entity\Step $step)
    {
        $this->step = $step;
        $this->min_tickets = $step->getMinTickets();

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set ticketsSold
     *
     * @param integer $ticketsSold
     *
     * @return ContractArtist
     */
    public function setTicketsSold($ticketsSold)
    {
        $this->tickets_sold = $ticketsSold;

        return $this;
    }

    /**
     * Get ticketsSold
     *
     * @return integer
     */
    public function getTicketsSold()
    {
        return $this->tickets_sold;
    }

    /**
     * Set ticketsSent
     *
     * @param boolean $ticketsSent
     *
     * @return ContractArtist
     */
    public function setTicketsSent($ticketsSent)
    {
        $this->tickets_sent = $ticketsSent;

        return $this;
    }

    /**
     * Get ticketsSent
     *
     * @return boolean
     */
    public function getTicketsSent()
    {
        return $this->tickets_sent;
    }

    /**
     * Set mainArtist
     *
     * @param \AppBundle\Entity\Artist $mainArtist
     *
     * @return ContractArtist
     */
    public function setMainArtist(\AppBundle\Entity\Artist $mainArtist = null)
    {
        $this->main_artist = $mainArtist;

        return $this;
    }

    /**
     * Get mainArtist
     *
     * @return \AppBundle\Entity\Artist
     */
    public function getMainArtist()
    {
        return $this->main_artist;
    }

    /**
     * Set nbClosingDays
     *
     * @param integer $nbClosingDays
     *
     * @return ContractArtist
     */
    public function setNbClosingDays($nbClosingDays)
    {
        $this->nb_closing_days = $nbClosingDays;

        return $this;
    }

    /**
     * Get nbClosingDays
     *
     * @return integer
     */
    public function getNbClosingDays()
    {
        return $this->nb_closing_days;
    }

    /**
     * Set minTickets
     *
     * @param integer $minTickets
     *
     * @return ContractArtist
     */
    public function setMinTickets($minTickets)
    {
        $this->min_tickets = $minTickets;

        return $this;
    }

    /**
     * Set ticketsReserved
     *
     * @param integer $ticketsReserved
     *
     * @return ContractArtist
     */
    public function setTicketsReserved($ticketsReserved)
    {
        $this->tickets_reserved = $ticketsReserved;

        return $this;
    }

    /**
     * Get ticketsReserved
     *
     * @return integer
     */
    public function getTicketsReserved()
    {
        return $this->tickets_reserved;
    }

    /**
     * Set sponsorshipReward
     *
     * @param Reward $sponsorshipReward
     *
     * @return ContractArtist
     */
    public function setSponsorshipReward(Reward $sponsorshipReward = null)
    {
        $this->sponsorship_reward = $sponsorshipReward;
        return $this;
    }

    /**
     * Get sponsorshipReward
     *
     * @return Reward
     */
    public function getSponsorshipReward()
    {
        return $this->sponsorship_reward;
    }

    /**
     * Set knownLineup
     *
     * @param boolean $knownLineup
     *
     * @return ContractArtist
     */
    public function setKnownLineup($knownLineup)
    {
        $this->known_lineup = $knownLineup;

        return $this;
    }

    /**
     * Get knownLineup
     *
     * @return boolean
     */
    public function getKnownLineup()
    {
        return $this->known_lineup;
    }

    /**
     * Get noThreshold
     *
     * @return boolean
     */
    public function getNoThreshold()
    {
        return $this->no_threshold;
    }

    /**
     * Add festivalday
     *
     * @param \AppBundle\Entity\FestivalDay $festivalday
     *
     * @return ContractArtist
     */
    public function addFestivalday(\AppBundle\Entity\FestivalDay $festivalday)
    {
        $this->festivaldays[] = $festivalday;

        return $this;
    }

    /**
     * Remove festivalday
     *
     * @param \AppBundle\Entity\FestivalDay $festivalday
     */
    public function removeFestivalday(\AppBundle\Entity\FestivalDay $festivalday)
    {
        $this->festivaldays->removeElement($festivalday);
    }

    /**
     * Get festivaldays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFestivaldays()
    {
        return $this->festivaldays;
    }

    /**
     * Add counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return ContractArtist
     */
    public function addCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counterParts[] = $counterPart;

        return $this;
    }
}
