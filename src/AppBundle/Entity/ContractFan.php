<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ContractFan
 *
 * @ORM\Table(name="contract_fan")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractFanRepository")
 */
class ContractFan
{
    const ORDERS_DIRECTORY = 'pdf/orders/';
    const TICKETS_DIRECTORY = 'pdf/tickets/';
    const VOTES_TO_REFUND = 2;

    public function __toString()
    {
        $str = '';

        for ($i = 0; $i < $this->purchases->count(); $i++) {
            if ($i > 0) {
                $str .= ', ';
            }
            $str .= $this->purchases->get($i);
        }

        return $str;
    }

    public function __construct(BaseContractArtist $ca)
    {
        $this->contractArtist = $ca;
        $this->purchases = new ArrayCollection();

        foreach ($ca->getCounterParts() as $cp) {
            $purchase = new Purchase();
            $purchase->setCounterpart($cp);
            $this->addPurchase($purchase);
        }

        $this->amount = 0;
        $this->counterparts_sent = false;
        $this->date = new \DateTime();
        $this->refunded = false;
        $this->tickets = new ArrayCollection();
        $this->user_rewards = new ArrayCollection();
        $this->ticket_rewards = new ArrayCollection();
    }

    public function isPaid()
    {
        return $this->getPaid();
    }

    public function isRefunded()
    {
        return $this->getRefunded();
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

    public function getTresholdIncrease() {
        return array_sum(array_map(function(Purchase $purchase) {
            return $purchase->getThresholdIncrease();
        }, $this->purchases->toArray()));
    }

    public function generateBarCode()
    {
        if (empty($this->barcode_text))
            $this->barcode_text = 'cf' . $this->id . uniqid();
    }

    public function generateTickets()
    {
        $this->generateBarCode();
        if (empty($this->tickets)) {
            foreach ($this->purchases as $purchase) {
                /** @var Purchase $purchase */
                for ($j = 1; $j < $purchase->getQuantity(); $j++) {
                    $counterPart = $purchase->getCounterpart();
                    $this->addTicket(new Ticket($this, $counterPart, $j));
                }
            }
        }
    }

    public function getOrderFileName()
    {
        return $this->getBarcodeText() . '.pdf';
    }

    public function getPdfPath()
    {
        return self::ORDERS_DIRECTORY . $this->getOrderFileName();
    }

    public function getTicketsPath()
    {
        return self::TICKETS_DIRECTORY . $this->getTicketsFileName();
    }

    public function getTicketsFileName()
    {
        return $this->getBarcodeText() . '-tickets.pdf';
    }

    public function getAmountWithoutReduction()
    {
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->getAmount();
        }, $this->purchases->toArray()));
    }

    public function getPaid()
    {
        return $this->cart->getPaid() && !$this->refunded;
    }

    public function getCounterPartsQuantity()
    {
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->getQuantity();
        }, $this->purchases->toArray()));
    }

    public function getCounterPartsQuantityOrganic()
    {
        return $this->getCounterPartsQuantity() - $this->getCounterPartsQuantityPromotional();
    }

    public function getCounterPartsQuantityPromotional()
    {
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->getNbFreeCounterparts();
        }, $this->purchases->toArray()));
    }

    public function getNbReducedCounterPart()
    {
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->getNbReducedCounterparts();
        }, $this->purchases->toArray()));
    }

    public function getUser()
    {
        return $this->getCart()->getUser();
    }

    public function getFan()
    {
        return $this->getUser();
    }

    public function calculatePromotions()
    {
        foreach ($this->purchases as $purchase) {
            $purchase->calculatePromotions();
        }
    }

    public function isEligibleForPromotion(Promotion $promotion)
    {
        return $this->date >= $promotion->getStartDate() && $this->date <= $promotion->getEndDate();
    }

    public function setUserRewards($user_rewards)
    {
        $this->user_rewards = $user_rewards;
        return $this;
    }

    public function giveOutReward()
    {
        $givedReward = [];
        $index = 0;
        foreach ($this->user_rewards as $user_reward) {
            $index = 0;
            foreach ($this->purchases as $purchase) {
                if ($user_reward instanceof ReductionReward) {
                    for ($i = 0; $i < $this->$purchase->getNbReducedCounterparts(); $i++) {
                        if ($givedReward[$index] == null) {
                            $givedReward[$index] = [];
                        }
                        $givedReward[$index] = array_push($givedReward[$index], "Réduction x1");
                        $index = $index + 1;
                    }
                } else if ($user_reward instanceof InvitationReward) {
                    $j = 1;
                    while ($j <= $purchase->getQuantity() && $j <= $user_reward->getRemainUse()) {

                    }
                } else if ($user_reward instanceof ConsomableReward) {

                }
            }
        }
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="BaseContractArtist", inversedBy="contractsFan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contractArtist;

    /**
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="contractFan", cascade={"all"})
     */
    private $purchases;

    /**
     * @ORM\Column(name="counterparts_sent", type="boolean")
     */
    private $counterparts_sent;

    /**
     * @ORM\Column(name="barcode_text", type="string", length=255, nullable=true)
     */
    private $barcode_text;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @ORM\OneToOne(targetEntity="Payment", mappedBy="contractFan")
     */
    private $payment;

    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="contractFan", cascade={"all"})
     */
    private $tickets;

    /**
     * @ORM\ManyToMany(targetEntity="User_Reward", inversedBy="contractFans", cascade={"persist"})
     */
    private $user_rewards;

    /**
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @ORM\OneToMany(targetEntity="RewardTicketConsumption", mappedBy="contractFan",cascade={"all"})
     */
    private $ticket_rewards;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinColumn(name="contract_fan_refund_request")
     */
    private $asking_refund;

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
     * Set contractArtist
     *
     * @param \AppBundle\Entity\ContractArtist $contractArtist
     *
     * @return ContractFan
     */
    public function setContractArtist(\AppBundle\Entity\ContractArtist $contractArtist)
    {
        $this->contractArtist = $contractArtist;

        return $this;
    }

    /**
     * Get contractArtist
     *
     * @return \AppBundle\Entity\ContractArtist
     */
    public function getContractArtist()
    {
        return $this->contractArtist;
    }

    /**
     * Add purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     *
     * @return ContractFan
     */
    public function addPurchase(\AppBundle\Entity\Purchase $purchase)
    {
        $this->purchases[] = $purchase;
        $purchase->setContractFan($this);

        return $this;
    }

    /**
     * Remove purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     */
    public function removePurchase(\AppBundle\Entity\Purchase $purchase)
    {
        $this->purchases->removeElement($purchase);
    }

    /**
     * Get purchases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return ContractFan
     */
    public function setCart(\AppBundle\Entity\Cart $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set counterpartsSent
     *
     * @param boolean $counterpartsSent
     *
     * @return ContractFan
     */
    public function setcounterpartsSent($counterpartsSent)
    {
        $this->counterparts_sent = $counterpartsSent;

        return $this;
    }

    /**
     * Get counterpartsSent
     *
     * @return boolean
     */
    public function getcounterpartsSent()
    {
        return $this->counterparts_sent;
    }

    /**
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return ContractFan
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcode_text = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcode_text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ContractFan
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
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return ContractFan
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
     * Set payment
     *
     * @param \AppBundle\Entity\Payment $payment
     *
     * @return ContractFan
     */
    public function setPayment(\AppBundle\Entity\Payment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \AppBundle\Entity\Payment
     */
    public function getPayment()
    {
        if($this->cart != null) {
            return $this->cart->getPayment();
        }
        return $this->payment;
    }

    /**
     * Add ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     *
     * @return ContractFan
     */
    public function addTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets[] = $ticket;

        return $this;
    }

    /**
     * Remove ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     */
    public function removeTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Add userReward
     *
     * @param \AppBundle\Entity\User_Reward $userReward
     *
     * @return ContractFan
     */
    public function addUserReward(\AppBundle\Entity\User_Reward $userReward)
    {
        $this->user_rewards[] = $userReward;
        return $this;
    }

    /**
     * Remove userReward
     *
     * @param \AppBundle\Entity\User_Reward $userReward
     */
    public function removeUserReward(\AppBundle\Entity\User_Reward $userReward)
    {
        $this->user_rewards->removeElement($userReward);
    }

    /**
     * Get userRewards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserRewards()
    {
        return $this->user_rewards;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return ContractFan
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Add ticketReward
     *
     * @param \AppBundle\Entity\RewardTicketConsumption $ticketReward
     *
     * @return ContractFan
     */
    public function addTicketReward(\AppBundle\Entity\RewardTicketConsumption $ticketReward)
    {
        if (!$this->ticket_rewards->contains($ticketReward)) {
            $this->ticket_rewards[] = $ticketReward;
            $ticketReward->setContractFan($this);
        }
        return $this;
    }

    /**
     * Remove ticketReward
     *
     * @param \AppBundle\Entity\RewardTicketConsumption $ticketReward
     */
    public function removeTicketReward(\AppBundle\Entity\RewardTicketConsumption $ticketReward)
    {
        if ($this->ticket_rewards->contains($ticketReward)) {
            $this->ticket_rewards->removeElement($ticketReward);
            $ticketReward->setContractFan(null);
        }
    }

    /**
     * Get ticketRewards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketRewards()
    {
        return $this->ticket_rewards;
    }
}
