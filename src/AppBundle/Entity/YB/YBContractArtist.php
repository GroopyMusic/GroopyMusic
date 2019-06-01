<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\Address;
use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Photo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * ContractArtist
 *
 * @ORM\Table(name="yb_contract_artist")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\YB\YBContractArtistRepository")
 * @Vich\Uploadable
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

    const UNCROWDABLE_STATES = [self::STATE_PASSED, self::STATE_FAILED, self::STATE_REFUNDED, self::STATE_SOLD_OUT, self::STATE_PENDING, self::STATE_SOLD_OUT_PENDING];
    const PENDING_STATES = [self::STATE_SUCCESS_PENDING, self::STATE_PENDING, self::STATE_SOLD_OUT_PENDING];
    const SOLDOUT_STATES = [self::STATE_SOLD_OUT, self::STATE_SOLD_OUT];
    const PASSED_STATES = [self::STATE_PASSED, self::STATE_FAILED, self::STATE_REFUNDED];
    const ONGOING_STATES = [self::STATE_ONGOING, self::STATE_SUCCESS_ONGOING];

    const PHOTOS_DIR = 'images/campaigns/';
    const PHOTOS_DIR_YB = 'yb/images/campaigns/';

    const DAYS_BEFORE_WAY_PASSED = 60;

    public static function getWebPath(Photo $photo) {
        return self::PHOTOS_DIR . $photo->getFilename();
    }

    public static function getYBWebPath(Photo $photo){
        return self::PHOTOS_DIR_YB . $photo->getFilename();
    }

    public function __construct()
    {
        parent::__construct();
        $this->tickets_sent = false;
        $this->date_closure = (new \DateTime())->add(new \DateInterval('P1M'));
        $this->sold_counterparts = 0;
        $this->code = uniqid();
        $this->commissions = new ArrayCollection();
        $this->transactional_messages = new ArrayCollection();
        $this->sub_events = new ArrayCollection();
        $this->no_sub_events = true;
        $this->date_event = new \DateTime();
        $this->external_invoice = 0;
        $this->published = false;
        $this->draft = true;
    }

    public function getBuyers() {
        if($this->getContractsFanPaid() == null || empty($this->getContractsFanPaid())) {
            return [];
        }
        return array_map(function(ContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getContractsFanPaid());
    }

    // Also return refunded buyers
    public function getWideBuyers() {
        if($this->getContractsFanPaidAndRefunded() == null || empty($this->getContractsFanPaidAndRefunded())) {
            return [];
        }
        return array_map(function(ContractFan $cf) {
            return $cf->getPhysicalPerson();
        }, $this->getContractsFanPaidAndRefunded());
    }

    /** @deprecated */
    public function isEvent() {
        return true;
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
        return $this->isPassed() && $this->getDateEvent()->diff(new \DateTime())->days > self::DAYS_BEFORE_WAY_PASSED;
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

    public function isSoldOutTicket(EntityManagerInterface $em){
        $available = 0;
        foreach ($this->counterParts as $cp){
            $available += $this->getNbSoldTicketFor($cp, $em);
        }
        return $available;
    }

    private function getNbSoldTicketFor(CounterPart $cp, EntityManagerInterface $em){
        if (in_array($cp, $this->counterParts->toArray())){
            $limit = $cp->getMaximumAmount();
            $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsFromEvent($this->getId(), $cp->getId());
            $nbTicket = count($tickets);
            return $limit - $nbTicket;
        } else {
            return -30;
        }
    }

    public function isToday(){
        $dates = [];
        if(!$this->hasSubEvents())
            $dates[] = $this->date_event->format('m/d/Y');
        else {
            foreach($this->sub_events as $se) {
                $dates[] = $se->getDate()->format('m/d/Y');
            }
        }
        $today = (new \DateTime())->format('m/d/Y');
        return in_array($today, $dates);
    }

    public function getTodaySubEvent(){
        foreach ($this->sub_events as $se){
            if ($se->getDate()->format('m/d/Y') === (new \DateTime())->format('m/d/Y')){
                return $se;
            }
        }
        return null;
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

                if ($this->successful) {
                    return $this->state = self::STATE_SUCCESS_ONGOING;
                }
                // if($this->getNbCounterPartsPaid() >= $max_cp) {
                //     return $this->state = self::STATE_SOLD_OUT_PENDING;
                // }

                if($this->dateEnd > $today) {
                    return $this->state = self::STATE_ONGOING;
                }
                return $this->state = self::STATE_PENDING;
            } else {
                return $this->state = self::STATE_PASSED;
            }
        }
    }

    public function isOutOfStockCp(CounterPart $cp){
        if ($cp->getContractArtist()->getConfig() == null){
            return $this->getNbPurchasable($cp) === 0;
        } else {
            $blocks = null;
            if ($cp->getContractArtist()->getConfig() !== null) {
                $blocks = $cp->getContractArtist()->getConfig()->getBlocks();
            }
            // cas où la salle n'est que en placement libre
            if ($this->config->isOnlyStandup() || $this->config->hasFreeSeatingPolicy()) {
                return ($this->getNbPurchasable($cp) === 0);
            } // si les blocs concernés par le CP sont en placement libre
            else if ($cp->hasOnlyFreeSeatingBlocks($blocks)) {
                return ($this->getNbPurchasable($cp) === 0);
            } // si les blocs ne sont que assis
            else if ($cp->hasOnlySeatedBlock($blocks)) {
                $totalSeatedCapacity = 0;
                $totalSold = 0;
                /** @var Block $block */
                foreach ($cp->getVenueBlocks() as $block) {
                    $totalSeatedCapacity += $block->getSeatedCapacity();
                    $totalSold += $block->getSoldTicketInBlock($cp->getContractArtist());
                }
                $realCapacity = min($totalSeatedCapacity, $cp->getMaximumAmount());
                return $totalSold === $realCapacity;
            } else {
                // mix des 2 (assis et debout)
                $cpCapacity = $cp->getMaximumAmount();
                $totalSoldSeated = 0;
                // on regarde d'abord les blocs assis
                /** @var Block $block */
                foreach ($cp->getVenueBlocks() as $block) {
                    $blkCapacity = $block->getComputedCapacity();
                    $soldInBlock = $block->getSoldTicketInBlock($this);
                    $realCapacity = min($cpCapacity, $blkCapacity);
                    if ($block->getType() === Block::BALCONY || $block->getType() === Block::SEATED) {
                        if ($soldInBlock < $realCapacity) {
                            // on sait qu'on peut encore mettre au moins 1 personne dans ce bloc
                            return false;
                        }
                    }
                    $totalSoldSeated += $soldInBlock;
                }
                // on est arrivé à la fin de la boucle : tous les blocs assis sont soldout
                // on regarde pour les debout
                return ($this->getNbPurchasable($cp) === 0);
            }
        }
    }

    /**
     * @return float|int
     */
    public function getTotalNbAvailable() {
        return $this->getMaxCounterParts() - $this->getTotalSoldCounterParts();
    }

    /**
     * @return string
     */
    public function getOrganizationName() {
        return $this->organization->getName();
    }

    /**
     * @return bool
     */
    public function hasSubEvents() {
        return !$this->no_sub_events;
    }

    /**
     * @return array
     */
    public function getSubEventsDates() {
        return array_map(function(YBSubEvent $subEvent) {
            return $subEvent->getDate();
        }, $this->sub_events->toArray());
    }

    public function isFacturable() {
        if($this->external_invoice)
            return false;

        if($this->isBroker() && $this->vat_number == null)
            return false;

        if($this->commissions == null || count($this->commissions) == 0)
            return false;

        if(count($this->getContractsFanPaid()) == 0)
            return false;

        return $this->contractsFanPaid[count($this->contractsFanPaid) - 1]->getInvoice() == null;
    }

    // Commissionnaire
    public function isBroker() {
        return $this->vat == 0 || $this->vat == null;
    }
    // Courtier 
    public function isCommissionary() {
        return !$this->isBroker();
    }

    public function togglePublicity() {
        $this->setPublished(!$this->published);
    }

    public function toggleDraft() {
        $this->setDraft(!$this->draft);
    }

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="yb_campaign_header", fileNameProperty="fileName", size="imageSize")
     *
     * @var File
     */
    private $imageFile;
    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    private $filename;
    private $imageSize;

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        // It is required that at least one field changes if you are using doctrine
        // otherwise the event listeners won't be called and the file is lost
        $this->updatedAt = new \DateTime();
    }

    public function setImageSize($imageSize)
    {
        $this->imageSize = $imageSize;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    public function getFilename() {
        return $this->filename;
    }

    public function getImageSize() {
        return $this->imageSize;
    }

    public function getImageFile() {
        return $this->imageFile;
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBSubEvent", mappedBy="campaign", cascade="persist")
     */
    private $sub_events;

    /**
     * @ORM\Column(name="no_sub_events", type="boolean")
     */
    private $no_sub_events;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="yb_campaigns")
     */
    private $handlers;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Organization", inversedBy="campaigns", cascade={"persist"})
     */
    private $organization;

    /**
     * @var string
     * @var string
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var Address
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @var Venue
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\Venue", inversedBy="events", cascade={"persist"})
     */
    private $venue;

    /**
     * @var VenueConfig
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\VenueConfig", inversedBy="events", cascade={"persist"})
     */
    private $config;

    /**
     * @var float
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;

    /**
     * #var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBCommission", cascade={"all"}, mappedBy="campaign")
     */
    private $commissions;

    /**
     * @var  ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\YB\YBInvoice", cascade={"all"}, mappedBy="campaign")
     */
    private $invoices;

    /**
     * @var bool
     * @ORM\Column(name="external_invoice", type="boolean")
     */
    private $external_invoice;

    /**
     * @ORM\OneToMany(targetEntity="YBTransactionalMessage", cascade={"remove"}, mappedBy="campaign")
     */
    private $transactional_messages;

    /**
     * @var string
     * @ORM\Column(name="bank_account", type="string", length=50, nullable=true)
     */
    private $bank_account;

    /**
     * @var string
     * @ORM\Column(name="vat_number", type="string", length=50, nullable=true)
     */
    private $vat_number;

    /**
     * @var
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\YB\CustomTicket", mappedBy="campaign")
     */
    private $customTicket;

    /**
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

    /**
     * @ORM\Column(name="draft", type="boolean")
     */
    private $draft;

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
        // For compatibility, this function returns the last event date when event is multidates
        if($this->hasSubEvents() && count($this->sub_events) > 0) {
            return $this->sub_events->last()->getDate();
        }

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
     * @return array
     */
    public function getHandlers()
    {
        return $this->organization->getMembers();
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
        if ($this->venue !== null){
            return $this->venue->getAddress();
        } else {
            return $this->address;
        }
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
        $this->commissions->clear();
        foreach($commissions as $commission) {
            $this->addCommission($commission);
        }
        return $this;
    }

    public function addCommission(YBCommission $commission) {
        $this->commissions->add($commission);
        $commission->setCampaign($this);
        return $this;
    }

    public function removeCommission(YBCommission $commission) {
        $this->commissions->remove($commission);
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
     * @return YBInvoice[]
     */
    public function getInvoices()
    {
        return array_filter($this->invoices->toArray(), function(YBInvoice $invoice) {
            return !$invoice->isDeleted();
        });
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

    public function getOrganization(){
        return $this->organization;
    }

    public function setOrganization($organization){
        $this->organization = $organization;

        if($this->organization != null) {
            if($this->vat_number == null) {
                $this->vat_number = $organization->getVatNumber();
            }

            if($this->bank_account == null) {
                $this->bank_account = $organization->getBankAccount();
            }
        }
    }

    public function getOrganizers(){
        if ($this->organization !== null){
            return $this->organization->getMembers();
        } else {
            return array();
        }
    }

    public function getVatNumber() {
        return $this->vat_number;
    }
    public function setVatNumber($vat_number) {
        $this->vat_number = $vat_number;
        return $this;
    }

    public function getBankAccount()
    {
        return $this->bank_account;
    }

    public function setBankAccount($bank_account)
    {
        $this->bank_account = $bank_account;
        return $this;
    }

    /**
     * @param array|ArrayCollection $sub_events
     * @return YBContractArtist
     */
    public function setSubEvents($sub_events)
    {
        $this->sub_events->clear();
        foreach($sub_events as $sub_event) {
            $this->addSubEvent($sub_event);
        }
        return $this;
    }

    public function addSubEvent(YBSubEvent $sub_event) {
        $this->sub_events->add($sub_event);
        $sub_event->setCampaign($this);
        return $this;
    }

    public function removeSubEvent(YBSubEvent $sub_event) {
        $this->sub_events->remove($sub_event);
        return $this;
    }

    public function getSubEvents()
    {
        return $this->sub_events;
    }

    /**
     * @return bool
     */
    public function getNoSubEvents()
    {
        return $this->no_sub_events;
    }

    /**
     * @param bool $no_sub_events
     * @return $this
     */
    public function setNoSubEvents($no_sub_events)
    {
        $this->no_sub_events = $no_sub_events;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExternalInvoice()
    {
        return $this->external_invoice;
    }

    /**
     * @param bool $external_invoice
     */
    public function setExternalInvoice($external_invoice)
    {
        $this->external_invoice = $external_invoice;
    }

    public function getConfig()
    {
        return $this->config;
    }
    public function setConfig($config)
    {
        $this->config = $config;
    }
    /**
     * @return Venue
     */
    public function getVenue()
    {
        return $this->venue;
    }
    /**
     * @param Venue $venue
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;
    }

    /**
     * @return mixed
     */
    public function getCustomTicket()
    {
        return $this->customTicket;
    }

    /**
     * @param mixed $customTicket
     */
    public function setCustomTicket($customTicket)
    {
        $this->customTicket = $customTicket;
    }

    public function setPublished($published) {
        $this->published = $published;
        return $this;
    }
    public function getPublished() {
        return $this->published;
    }
    public function isPublished() {
        return $this->getPublished();
    }

    public function setDraft($draft) {
        $this->draft = $draft;
        return $this;
    }
    public function getDraft() {
        return $this->draft;
    }
    public function isDraft() {
        return $this->getDraft();
    }
}
