<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 01/05/2018
 * Time: 15:02
 */

namespace AppBundle\Entity;

/**
 * User_Reward
 *
 * @ORM\Table(name="reward_ticket_consumption")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RewardTicketConsumption")
 */
class RewardTicketConsumption
{
    public function __construct()
    {

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
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticket;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;
}