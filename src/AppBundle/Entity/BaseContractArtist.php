<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="base_contract_artist")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"concert" = "ContractArtist", "default" = "BaseContractArtist"})
 */
class BaseContractArtist
{
    const VOTES_TO_REFUND = 2;

    public function __toString()
    {
        return 'Evénement #'.$this->id. ' de l\'artiste '. $this->artist;
    }

    public function __construct() {
        $this->accept_conditions = false;
        $this->reminders_artist = 0;
        $this->reminders_admin = 0;
        $this->date = new \DateTime();
        $this->collected_amount = 0;
        $this->failed = false;
        $this->successful = false;
        $this->cart_reminder_sent = false;
        $this->refunded = false;
        $this->asking_refund = new ArrayCollection();
    }

    public function isRefundReady() {
        return count($this->asking_refund) >= self::VOTES_TO_REFUND;
    }

    public function isAskedRefundBy(User $user) {
        return $this->asking_refund->contains($user);
    }

    public function isAskedRefundByOne() {
        return count($this->asking_refund) >= 1;
    }

    public function isOneStepFromBeingRefunded() {
        return self::VOTES_TO_REFUND - count($this->asking_refund) == 1;
    }

    public function addAmount($amount) {
        $this->collected_amount += $amount;
    }

    public function isSoldOut() {
        foreach($this->getStep()->getCounterParts() as $cp) {
            if($this->getNbAvailable($cp) > 0) {
                return false;
            }
        }
        return true;
    }

    public function getNbAvailable(CounterPart $cp) {
        $nb = $cp->getMaximumAmount();

        foreach($this->contractsFan as $cf) {
            foreach($cf->getPurchases() as $purchase) {
                if(!$cf->getRefunded() && $purchase->getCounterPart()->getId() == $cp->getId()) {
                    $nb -= $purchase->getQuantity();
                }
            }
        }

        if($nb <= 0) return 0;
        return $nb;
    }

    public function cantAddPurchase($quantity, CounterPart $cp) {
        return $this->getNbAvailable($cp) < $quantity;
    }

    public function getArtistProfiles() {
        $result = [];

        foreach($this->artist->getArtistsUser() as $artist_user) {
            $result[] = $artist_user->getUser();
        }

        return $result;
    }

    public function getFanProfiles() {
        $fans = [];

        foreach($this->contractsFan as $cf) {
            $fans[] = $cf->getUser();
        }

        return $fans;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    protected $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime")
     */
    protected $dateEnd;

    /**
     * @var Artist
     *
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="base_contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $artist;

    /**
     * @ORM\Column(name="motivations", type="text", nullable=true)
     */
    protected $motivations;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="contractArtist")
     */
    protected $payments;

    /**
     * @ORM\Column(name="reminders_artist", type="smallint")
     */
    protected $reminders_artist;

    /**
     * @var ContractArtistPossibility
     *
     * @ORM\OneToOne(targetEntity="ContractArtistPossibility", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $preferences;

    /**
     * @var ContractArtistPossibility
     *
     * @ORM\OneToOne(targetEntity="ContractArtistPossibility", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $reality;

    /**
     * @ORM\Column(name="collected_amount", type="integer")
     */
    protected $collected_amount;

    /**
     * @ORM\OneToMany(targetEntity="ContractFan", mappedBy="contractArtist", cascade={"persist"})
     */
    protected $contractsFan;

    /**
     * @ORM\Column(name="failed", type="boolean")
     */
    protected $failed;

    /**
     * @ORM\Column(name="successful", type="boolean")
     */
    protected $successful;

    /**
     * @ORM\Column(name="cart_reminder_sent", type="boolean")
     */
    protected $cart_reminder_sent;

    /**
     * @ORM\Column(name="refunded", type="boolean")
     */
    protected $refunded;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     */
    protected $asking_refund;

    /**
     * @ORM\ManyToOne(targetEntity="Newsletter", inversedBy="contracts")
     */
    protected $newsletter;

    /**
     * @ORM\Column(name="reminders_admin", type="smallint")
     */
    protected $reminders_admin;

    /**
     * @ORM\Column(name="last_reminder_admin", type="datetime", nullable=true)
     */
    protected $last_reminder_admin;

    // Discriminator
    protected $type;

    // Conditions approval (user form only)
    protected $accept_conditions;

    // Deadline calculation
    protected $theoritical_deadline;

    /**
     * Get id
     *
     * @return int
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
     * @return BaseContractArtist
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
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return BaseContractArtist
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return BaseContractArtist
     */
    public function setArtist(\AppBundle\Entity\Artist $artist)
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
     * @return mixed
     */
    public function getTheoriticalDeadline()
    {
        return $this->theoritical_deadline;
    }

    /**
     * @param mixed $theoritical_deadline
     */
    public function setTheoriticalDeadline($theoritical_deadline)
    {
        $this->theoritical_deadline = $theoritical_deadline;
    }

    /**
     * @return boolean
     */
    public function getAcceptConditions()
    {
        return $this->accept_conditions;
    }

    /**
     * @param boolean $accept_conditions
     */
    public function setAcceptConditions($accept_conditions)
    {
        $this->accept_conditions = $accept_conditions;
    }

    /**
     * Set motivations
     *
     * @param string $motivations
     *
     * @return BaseContractArtist
     */
    public function setMotivations($motivations)
    {
        $this->motivations = $motivations;

        return $this;
    }

    /**
     * Get motivations
     *
     * @return string
     */
    public function getMotivations()
    {
        return $this->motivations;
    }

    /**
     * Add payment
     *
     * @param \AppBundle\Entity\Payment $payment
     *
     * @return BaseContractArtist
     */
    public function addPayment(\AppBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \AppBundle\Entity\Payment $payment
     */
    public function removePayment(\AppBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set reminders
     *
     * @param integer $reminders
     *
     * @return BaseContractArtist
     */
    public function setRemindersArtist($reminders)
    {
        $this->reminders_artist = $reminders;

        return $this;
    }

    /**
     * Get reminders
     *
     * @return integer
     */
    public function getRemindersArtist()
    {
        return $this->reminders_artist;
    }

    /**
     * Set preferences
     *
     * @param \AppBundle\Entity\ContractArtistPossibility $preferences
     *
     * @return BaseContractArtist
     */
    public function setPreferences(\AppBundle\Entity\ContractArtistPossibility $preferences = null)
    {
        $this->preferences = $preferences;
        $preferences->setContract($this);

        return $this;
    }

    /**
     * Get preferences
     *
     * @return \AppBundle\Entity\ContractArtistPossibility
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * Set collectedAmount
     *
     * @param integer $collectedAmount
     *
     * @return BaseContractArtist
     */
    public function setCollectedAmount($collectedAmount)
    {
        $this->collected_amount = $collectedAmount;

        return $this;
    }

    /**
     * Get collectedAmount
     *
     * @return integer
     */
    public function getCollectedAmount()
    {
        return $this->collected_amount;
    }

    /**
     * Set failed
     *
     * @param boolean $failed
     *
     * @return BaseContractArtist
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed
     *
     * @return boolean
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set successful
     *
     * @param boolean $successful
     *
     * @return BaseContractArtist
     */
    public function setSuccessful($successful)
    {
        $this->successful = $successful;

        return $this;
    }

    /**
     * Get successful
     *
     * @return boolean
     */
    public function getSuccessful()
    {
        return $this->successful;
    }

    /**
     * Set reality
     *
     * @param \AppBundle\Entity\ContractArtistPossibility $reality
     *
     * @return BaseContractArtist
     */
    public function setReality(\AppBundle\Entity\ContractArtistPossibility $reality = null)
    {
        $this->reality = $reality;

        if($reality != null)
            $reality->setContract($this);

        return $this;
    }

    /**
     * Get reality
     *
     * @return \AppBundle\Entity\ContractArtistPossibility
     */
    public function getReality()
    {
        return $this->reality;
    }

    /**
     * Add contractsFan
     *
     * @param \AppBundle\Entity\ContractFan $contractsFan
     *
     * @return BaseContractArtist
     */
    public function addContractsFan(\AppBundle\Entity\ContractFan $contractsFan)
    {
        $this->contractsFan[] = $contractsFan;

        return $this;
    }

    /**
     * Remove contractsFan
     *
     * @param \AppBundle\Entity\ContractFan $contractsFan
     */
    public function removeContractsFan(\AppBundle\Entity\ContractFan $contractsFan)
    {
        $this->contractsFan->removeElement($contractsFan);
    }

    /**
     * Get contractsFan
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContractsFan()
    {
        return $this->contractsFan;
    }

    /**
     * Set cartReminderSent
     *
     * @param boolean $cartReminderSent
     *
     * @return BaseContractArtist
     */
    public function setCartReminderSent($cartReminderSent)
    {
        $this->cart_reminder_sent = $cartReminderSent;

        return $this;
    }

    /**
     * Get cartReminderSent
     *
     * @return boolean
     */
    public function getCartReminderSent()
    {
        return $this->cart_reminder_sent;
    }

    /**
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return BaseContractArtist
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return boolean
     */
    public function getRefunded()
    {
        return $this->refunded;
    }

    /**
     * Add askingRefund
     *
     * @param \AppBundle\Entity\User $askingRefund
     *
     * @return BaseContractArtist
     */
    public function addAskingRefund(\AppBundle\Entity\User $askingRefund)
    {
        $this->asking_refund[] = $askingRefund;

        return $this;
    }

    /**
     * Remove askingRefund
     *
     * @param \AppBundle\Entity\User $askingRefund
     */
    public function removeAskingRefund(\AppBundle\Entity\User $askingRefund)
    {
        $this->asking_refund->removeElement($askingRefund);
    }

    /**
     * Get askingRefund
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAskingRefund()
    {
        return $this->asking_refund;
    }

    /**
     * Set newsletter
     *
     * @param \AppBundle\Entity\Newsletter $newsletter
     *
     * @return BaseContractArtist
     */
    public function setNewsletter(\AppBundle\Entity\Newsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \AppBundle\Entity\Newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set remindersAdmin
     *
     * @param integer $remindersAdmin
     *
     * @return BaseContractArtist
     */
    public function setRemindersAdmin($remindersAdmin)
    {
        $this->reminders_admin = $remindersAdmin;

        return $this;
    }

    /**
     * Get remindersAdmin
     *
     * @return integer
     */
    public function getRemindersAdmin()
    {
        return $this->reminders_admin;
    }


    /**
     * Set lastReminderAdmin
     *
     * @param \DateTime $lastReminderAdmin
     *
     * @return BaseContractArtist
     */
    public function setLastReminderAdmin($lastReminderAdmin)
    {
        $this->last_reminder_admin = $lastReminderAdmin;

        return $this;
    }

    /**
     * Get lastReminderAdmin
     *
     * @return \DateTime
     */
    public function getLastReminderAdmin()
    {
        return $this->last_reminder_admin;
    }
}
