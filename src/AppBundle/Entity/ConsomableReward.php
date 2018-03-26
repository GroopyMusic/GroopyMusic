<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 12:40
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvitationReward
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConsomableRewardRepository")
 */
class ConsomableReward extends Reward
{
    public function __construct()
    {
    }

    /**
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(name="type_consomable", type="string", length=255)
     */
    private $type_consomable;


    /**
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return ConsomableReward
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

    /**
     * Set value
     *
     * @param float $value
     *
     * @return ConsomableReward
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set typeConsomable
     *
     * @param string $typeConsomable
     *
     * @return ConsomableReward
     */
    public function setTypeConsomable($typeConsomable)
    {
        $this->type_consomable = $typeConsomable;

        return $this;
    }

    /**
     * Get typeConsomable
     *
     * @return string
     */
    public function getTypeConsomable()
    {
        return $this->type_consomable;
    }
}
