<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 12:20
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InvitationReward
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InvitationRewardRepository")
 */
class InvitationReward extends Reward
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $start_date;


    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $end_date;


    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return InvitationReward
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return InvitationReward
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
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
