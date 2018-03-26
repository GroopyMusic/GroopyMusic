<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 14:55
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReductionReward
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReductionRewardRepository")
 */
class ReductionReward extends Reward
{
    public function __construct()
    {
    }

    /**
     * @ORM\Column(name="reduction", type="integer")
     */
    private $quantity;


    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return ReductionReward
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
