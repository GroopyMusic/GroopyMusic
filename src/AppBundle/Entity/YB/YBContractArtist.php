<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Address;
use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Photo;
use Doctrine\Common\Collections\ArrayCollection;
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
    const STATE_SUCCESS_PENDING = 'state.success.pending';
    const STATE_SUCCESS_ONGOING = 'state.success.ongoing';
    const STATE_PENDING = 'state.pending';
    const STATE_SOLD_OUT_PENDING = 'state.soldout.pending';
    const STATE_WAY_PASSED = 'state.way_passed';

    const UNCROWDABLE_STATES = [self::STATE_PASSED, self::STATE_FAILED, self::STATE_REFUNDED, self::STATE_SOLD_OUT, self::STATE_PENDING, self::STATE_SOLD_OUT_PENDING];
    const PENDING_STATES = [self::STATE_SUCCESS_PENDING, self::STATE_PENDING, self::STATE_SOLD_OUT_PENDING];
    const SOLDOUT_STATES = [self::STATE_SOLD_OUT, self::STATE_SOLD_OUT];
    const PASSED_STATES = [self::STATE_PASSED, self::STATE_FAILED, self::STATE_REFUNDED, self::STATE_WAY_PASSED];
    const ONGOING_STATES = [self::STATE_ONGOING, self::STATE_SUCCESS_ONGOING];
    const WAY_PASSED_STATES = [self::STATE_WAY_PASSED];

    const PHOTOS_DIR = 'images/campaigns/';

    const DAYS_BEFORE_WAY_PASSED = 60;

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public function __construct()
    {
        parent::__construct();
        $this->tickets_sent = false;
        $this->date_closure = new \DateTime();
        $this->sold_counterparts = 0;
        $this->code = uniqid();
        $this->commissions = new ArrayCollection();
        $this->transactional_messages = new ArrayCollection();
    }

    public function getBuyers() {
        if($this->getContractsFanPaid() == null || empty($this->getContractsFanPaid())) {
            return [];
        }
        return array_map(function(ContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getContractsFanPaid());
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

    public function isPending() {
        return in_array($this->getState(), self::PENDING_STATES);
    }

    public function isSoldOut() {
        return in_array($this->getState(), self::SOLDOUT_STATES);
    }

    public function isPassed() {
        return in_array($this->getState(), self::PASSED_STATES);
    }

    public function isWayPassed() {
        return in_array($this->getState(), self::WAY_PASSED_STATES);
    }

    public function isOngoing() {
        return in_array($this->getState(), self::ONGOING_STATES);
    }

    public function isPendingSuccessful() {
        return $this->getState() == self::STATE_SUCCESS_PENDING;
    }

    public function getPercentObjective() {
        return min(floor(($this->getCounterpartsSold() / max(1, $this->getThreshold())) * 100), 100);
    }

    public function getState()
    {
        if ($this->state != null) {
            return $this->state;
        }

        $today = new \DateTime();

        $max_cp = $this->getMaxCounterParts();

        // Failure & refunded
        if ($this->refunded)
            return $this->state = self::STATE_REFUNDED;

        // Marked as failure
        if ($this->failed)
            return $this->state = self::STATE_FAILED;

        if ($this->no_threshold) {
            if ($this->getNbCounterPartsPaid() >= $max_cp) {
                return $this->state = self::STATE_SOLD_OUT;
            }

            if ($this->date_closure >= $today) {
                return $this->state = self::STATE_ONGOING;
            } else {
                return $this->state = self::STATE_PASSED;
            }
        } else {
            if ($this->date_closure >= $today) {
                if ($this->sold_counterparts >= $this->threshold && !$this->successful) {
                    if ($this->getNbCounterPartsPaid() >= $max_cp) {
                        return $this->state = self::STATE_SOLD_OUT_PENDING;
                    }

                    return $this->state = self::STATE_SUCCESS_PENDING;
                }
                if ($this->dateEnd >= $today) {
                    return $this->state = self::STATE_ONGOING;
                } else {
                    if ($this->successful) {
                        return $this->state = self::STATE_SUCCESS_ONGOING;
                    }
                    // if($this->getNbCounterPartsPaid() >= $max_cp) {
                    //     return $this->state = self::STATE_SOLD_OUT_PENDING;
                    // }
                    return $this->state = self::STATE_PENDING;
                }
            } else {
                if($this->date_event != null && $this->date_event < $today && $this->date_event->diff($today)->days > self::DAYS_BEFORE_WAY_PASSED)
                    return $this->state = self::STATE_WAY_PASSED;
                return $this->state = self::STATE_PASSED;
            }
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
     * @ORM\Column(name="sold_counterparts", type="float")
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
     * @var string
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(name="vat", type="float")
     */
    private $vat;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBCommission", cascade={"all"}, mappedBy="campaign")
     */
    private $commissions;

    /**
     * @ORM\OneToMany(targetEntity="YBTransactionalMessage", cascade={"remove"}, mappedBy="campaign")
     */
    private $transactional_messages;

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

    /**
     * Set code
     *
     * @param string $code
     *
     * @return YBContractArtist
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


    /**
     * @return mixed
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param mixed $vat
     * @return YBContractArtist
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @param $commissions
     * @return YBContractArtist
     */
    public function setCommissions($commissions)
    {
        $this->commissions = $commissions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommissions()
    {
        return $this->commissions;
    }

    /**
     * @return mixed
     */
    public function getTransactionalMessages()
    {
        return $this->transactional_messages;
    }

    /**
     * @param mixed $transactional_messages
     */
    public function setTransactionalMessages($transactional_messages)
    {
        $this->transactional_messages = $transactional_messages;
    }
}
