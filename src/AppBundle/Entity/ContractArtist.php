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
        parent::__construct();
        $this->tickets_sold = 0;
        $this->tickets_reserved = 0;
        $this->tickets_sent = false;
        $this->nb_closing_days = self::NB_DAYS_OF_CLOSING;
        $this->min_tickets = 0;
        $this->festivaldays = new ArrayCollection();
        $this->known_lineup = false;
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

    public function getDisplayDates() {
        $str = '';
        $i = 1;
        foreach($this->getFestivalDates() as $date) {
            if($i > 1)
                $str .= ' - ';
            $str .= $date->format('d/m/Y');
            $i++;
        }
        return $str;
    }

    public function getTitleWithDates() {
        $str = $this->getTitle();
        if(!empty($this->getFestivalDates())) {
            $str .= ' (';
            $i = 1;
            foreach($this->getFestivalDates() as $date) {
                if($i > 1)
                    $str .= ' - ';
                $str .= $date->format('d/m');
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
        return $this->tickets_reserved + $this->getCounterpartsSold();
    }

    public function getTotalBookedTicketsRaw() {
        return $this->tickets_reserved + $this->getNbCounterPartsPaid();
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

    /** @deprecated  */
    public function getTicketsSold() {
        return $this->getCounterpartsSold();
    }

    public function getTicketsSoldMajored() {
        return min($this->getCounterPartsSold(), $this->getMaxTickets());
    }

    public function getMaxTickets() {
        if(!empty($this->getFestivaldays())) {
            $normal_soldout = array_sum(array_map(function(FestivalDay $festivalDay) {
                return $festivalDay->getMaxTickets();
            }, $this->festivaldays->toArray()));
        }
        else {
            $normal_soldout = array_sum(array_map(function(CounterPart $counterPart) {
                return $counterPart->getMaximumAmount();
            }, $this->getCounterParts()->toArray()));
        }
        $global_soldout = $this->global_soldout == null ? $normal_soldout : $this->global_soldout;
        return min($global_soldout, $normal_soldout);
    }

    public function getTotalNbAvailable() {
        return $this->getMaxTickets() - $this->getTotalBookedTickets();
    }

    public function isValidatedBelowObjective() {
        return $this->isInSuccessfulState() && $this->getTicketsSold() < $this->getMinTickets();
    }

    public function getMinTickets() {
        if(!$this->hasNoThreshold() && $this->threshold <= 0 && $this->getStep() != null) {
            return $this->getStep()->getMinTickets();
        }
        else {
            return $this->threshold;
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

    /** @return array */
    public function getUniqueFestivalHalls() {
        return array_unique($this->getFestivalHalls());
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
        if(empty($this->getFestivalDates())) return null;
        return min($this->getFestivalDates());
    }

    public function getLastFestivalDate() {
        if(empty($this->getFestivalDates())) return null;
        return max($this->getFestivalDates());
    }

    /** @return string */
    public function getDisplayHalls() {
        $str = '';
        $i = 1;
        $halls = array_unique($this->getFestivalHalls());
        foreach($halls as $hall) {
            if($i > 1)
                $str .= ' - ';
            $str .= $hall->__toString();
            $i++;
        }
        return $str;
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

    public function getNbOrdersPaid() {
        return count($this->getContractsFanPaid());
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

        shuffle($this->all_artists);
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

    public function getFestivalDaysExport() {
        $i = 1;
        $exportList = [];
        foreach($this->getFestivaldays() as $festivalday) {
            /** @var FestivalDay $festivalday */
            $str = 'JOUR ' . $i . ' (' . $festivalday->getDate()->format('d/m/Y H:i') . ')  : ';

            $j = 0;
            foreach($festivalday->getArtistPerformances() as $performance) {
                if($j > 0)
                    $str .= ', ';

                $str .= $performance->__toString();

                $j++;
            }

            $i++;
            $exportList[] = $str;
        }
        return '<pre>' . join(PHP_EOL, $exportList) . '</pre>';
    }

    public function getContractsFanExport() {
        $cfs = $this->getContractsFanPaid();

        $cfs = array_map(function(ContractFan $cf) {
            return $cf->getPurchasesExport(true);
        }, $cfs);

        return '<pre>' . join(PHP_EOL, $cfs) . '</pre>';
    }

    protected $purchases = null;
    public function getPurchases() {
        if($this->purchases == null) {
            $cfs = $this->getContractsFanPaid();
            $purchases = [];
            foreach($cfs as $cf) {
                /** @var ContractFan $cf */
                foreach($cf->getPurchases() as $purchase) {
                    $purchases[] = $purchase;
                }
            }
            $this->purchases = $purchases;
        }
       return $this->purchases;
    }

    public function getArtistScoresExport() {
        $scoresList = [];
        $artists = [];

        $scoresList['all'] = 0;
        foreach($this->getAllArtists() as $artist) {
            $scoresList[$artist->getId()] = 0;
            $artists[$artist->getId()] = $artist->getArtistname();
        }

        foreach($this->getPurchases() as $purchase) {
            /** @var Purchase $purchase */
            if(!empty($purchase->getArtists()) && count($purchase->getArtists()) > 0) {
                foreach($purchase->getArtists() as $artist) {
                    $scoresList[$artist->getId()] = $scoresList[$artist->getId()] + $purchase->getThresholdIncrease();
                }
            }
            else {
                $scoresList['all'] = $scoresList['all'] + $purchase->getThresholdIncrease();
            }
        }

        $exportList = [];
        foreach($scoresList as $key => $value) {
            if($key != 'all') {
                $exportList[] = $artists[$key] . '  : ' . $value;
            }
            else {
                $exportList[] = 'Sans artiste particulier : ' . $value;
            }
        }

        return '<pre>' . join(PHP_EOL, $exportList) . '</pre>';
    }

    /**
     * @var Step
     *
     * @ORM\ManyToOne(targetEntity="Step")
     * @ORM\JoinColumn(nullable=true)
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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\FestivalDay", inversedBy="festivals", cascade={"all"})
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
        return $this->setThreshold($minTickets);
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

}
