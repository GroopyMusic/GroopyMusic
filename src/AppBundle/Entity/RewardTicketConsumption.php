<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 01/05/2018
 * Time: 15:02
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User_Reward
 *
 * @ORM\Table(name="reward_ticket_consumption")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RewardTicketConsumption")
 */
class RewardTicketConsumption
{

    /**
     * RewardTicketConsumption constructor.
     * @param $user_reward
     * @param $ticket
     * @param $contractFan
     * @param $purchase
     * @param bool $refunded
     * @param bool $refundable
     */
    public function __construct(User_Reward $user_reward, $ticket, $refunded, $refundable)
    {
        $this->user_reward = $user_reward;
        $this->ticket = $ticket;
        $this->refunded = $refunded;
        $this->refundable = $refundable;
        $user_reward->addTicket($this);
    }

    public function __toString()
    {
        return '';
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
     * @ORM\ManyToOne(targetEntity="User_Reward", inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_reward;

    /**
     * @ORM\ManyToOne(targetEntity="Ticket", inversedBy="rewards")
     */
    private $ticket;

    /**
     * @ORM\ManyToOne(targetEntity="ContractFan", inversedBy="ticket_rewards")
     */
    private $contractFan;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase", inversedBy="ticket_rewards")
     */
    private $purchase;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refundable", type="boolean")
     */
    private $refundable;

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
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return RewardTicketConsumption
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
     * Set refundable
     *
     * @param boolean $refundable
     *
     * @return RewardTicketConsumption
     */
    public function setRefundable($refundable)
    {
        $this->refundable = $refundable;

        return $this;
    }

    /**
     * Get refundable
     *
     * @return boolean
     */
    public function getRefundable()
    {
        return $this->refundable;
    }

    /**
     * Set userReward
     *
     * @param \AppBundle\Entity\User_Reward $userReward
     *
     * @return RewardTicketConsumption
     */
    public function setUserReward(\AppBundle\Entity\User_Reward $userReward)
    {
        $this->user_reward = $userReward;

        return $this;
    }

    /**
     * Get userReward
     *
     * @return \AppBundle\Entity\User_Reward
     */
    public function getUserReward()
    {
        return $this->user_reward;
    }

    /**
     * Set ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     *
     * @return RewardTicketConsumption
     */
    public function setTicket(\AppBundle\Entity\Ticket $ticket = null)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \AppBundle\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     *
     * @return RewardTicketConsumption
     */
    public function setContractFan(\AppBundle\Entity\ContractFan $contractFan = null)
    {
        $this->contractFan = $contractFan;

        return $this;
    }

    /**
     * Get contractFan
     *
     * @return \AppBundle\Entity\ContractFan
     */
    public function getContractFan()
    {
        return $this->contractFan;
    }

    /**
     * Set purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     *
     * @return RewardTicketConsumption
     */
    public function setPurchase(\AppBundle\Entity\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \AppBundle\Entity\Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }
}
