<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Photo;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContractArtist
 *
 * @ORM\Table(name="yb_contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\YBContractArtistRepository")
 */
class YBContractArtist extends BaseContractArtist
{
    const STATE_FAILED = 'state.failed';
    const STATE_REFUNDED = 'state.refunded';
    const STATE_SOLD_OUT = 'state.soldout';
    const STATE_ONGOING = 'state.ongoing';
    const STATE_PASSED = 'state.passed';

    const UNCROWDABLE_STATES = [self::STATE_PASSED, self::STATE_FAILED, self::STATE_REFUNDED, self::STATE_SOLD_OUT];

    const PHOTOS_DIR = 'yb/images/campaigns/';

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public function __construct()
    {
        parent::__construct();
        $this->tickets_sent = false;
    }

    public function isEvent() {
        return $this->getDateEvent() != null;
    }

    public function isUncrowdable() {
        return in_array($this->getState(), self::UNCROWDABLE_STATES);
    }

    public function isCrowdable() {
        return !$this->isUncrowdable();
    }

    public function getState() {
        if(isset($this->state)) {
            return $this->state;
        }

        $today = new \DateTime();

        $max_cp = $this->getMaxCounterParts();

        // Failure & refunded
        if($this->refunded)
            return self::STATE_REFUNDED;

        // Marked as failure
        if($this->failed)
            return self::STATE_FAILED;

        if($this->getNbCounterPartsPaid() >= $max_cp) {
            return self::STATE_SOLD_OUT;
        }

        if($this->no_threshold) {
            if($this->date_closure >= $today) {
                return self::STATE_ONGOING;
            }
            else {
                return self::STATE_PASSED;
            }
        }

        else {
            // TODO !!!!
        }
    }

    public function getMaxCounterParts() {
        $normal_soldout = array_sum(array_map(function(CounterPart $counterPart) {
            return $counterPart->getMaximumAmount();
        }, $this->getCounterParts()->toArray()));

        $global_soldout = $this->global_soldout == null ? $normal_soldout : $this->global_soldout;
        return min($global_soldout, $normal_soldout);
    }

    public function getTotalNbAvailable() {
        return $this->getMaxCounterParts() - $this->getTotalSoldCounterParts();
    }

    /**
     * @var integer
     * @ORM\Column(name="sold_counterparts", type="integer")
     */
    private $sold_counterparts;

    /**
     * @var bool
     * @ORM\Column(name="tickets_sent", type="boolean")
     */
    private $tickets_sent;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_closure", type="datetime")
     */
    private $date_closure;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_event", type="datetime", nullable=true)
     */
    private $date_event;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="yb_campaigns")
     */
    private $handlers;

    /**
     * Set ticketsSent
     *
     * @param boolean $ticketsSent
     *
     * @return YBContractArtist
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
     * Set dateClosure
     *
     * @param \DateTime $dateClosure
     *
     * @return YBContractArtist
     */
    public function setDateClosure($dateClosure)
    {
        $this->date_closure = $dateClosure;

        return $this;
    }

    /**
     * Get dateClosure
     *
     * @return \DateTime
     */
    public function getDateClosure()
    {
        return $this->date_closure;
    }

    /**
     * Set dateEvent
     *
     * @param \DateTime $dateEvent
     *
     * @return YBContractArtist
     */
    public function setDateEvent($dateEvent)
    {
        $this->date_event = $dateEvent;

        return $this;
    }

    /**
     * Get dateEvent
     *
     * @return \DateTime
     */
    public function getDateEvent()
    {
        return $this->date_event;
    }

    /**
     * Set threshold
     *
     * @param integer $threshold
     *
     * @return YBContractArtist
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get threshold
     *
     * @return integer
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * Set soldCounterparts
     *
     * @param integer $soldCounterparts
     *
     * @return YBContractArtist
     */
    public function setSoldCounterparts($soldCounterparts)
    {
        $this->sold_counterparts = $soldCounterparts;

        return $this;
    }

    /**
     * Get soldCounterparts
     *
     * @return integer
     */
    public function getSoldCounterparts()
    {
        return $this->sold_counterparts;
    }

    /**
     * Set counterpartsSold
     *
     * @param float $counterpartsSold
     *
     * @return YBContractArtist
     */
    public function setCounterpartsSold($counterpartsSold)
    {
        $this->counterparts_sold = $counterpartsSold;

        return $this;
    }

    /**
     * Get counterpartsSold
     *
     * @return float
     */
    public function getCounterpartsSold()
    {
        return $this->counterparts_sold;
    }

    /**
     * Add handler
     *
     * @param \AppBundle\Entity\User $handler
     *
     * @return YBContractArtist
     */
    public function addHandler(\AppBundle\Entity\User $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Remove handler
     *
     * @param \AppBundle\Entity\User $handler
     */
    public function removeHandler(\AppBundle\Entity\User $handler)
    {
        $this->handlers->removeElement($handler);
    }

    /**
     * Get handlers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}
