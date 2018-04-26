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
        parent::__construct();
    }

    /**
     * @ORM\Column(name="reduction", type="integer")
     */
    private $reduction;


    /**
     * Set reduction
     *
     * @param integer $reduction
     *
     * @return ReductionReward
     */
    public function setReduction($reduction)
    {
        $this->reduction = $reduction;

        return $this;
    }

    /**
     * Get reduction
     *
     * @return integer
     */
    public function getReduction()
    {
        return $this->reduction;
    }

    public function getVariables()
    {
        $vars = [];
        $reflect = new \ReflectionClass(__CLASS__);
        foreach (get_object_vars($this) as $key => $value) {
            $property = $reflect->getProperty($key);
            if ($property != null && $property->class == __CLASS__) {
                $vars[$key] = $value;
            }
        }
        return $vars;
    }
}
