<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User_Conditions
 *
 * @ORM\Table(name="fos_user__conditions")
 * @ORM\Entity
 */
class User_Conditions
{
    public function __construct(User $user, Conditions $conditions)
    {
        $this->date = new \DateTime();
        $this->user = $user;
        $this->conditions = $conditions;
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
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="user_conditions", fetch="EAGER")
     */
    private $user;

    /**
     * @var Conditions
     * @ORM\ManyToOne(targetEntity="Conditions", fetch="EAGER")
     */
    private $conditions;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return User_Conditions
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return User_Conditions
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set conditions
     *
     * @param \AppBundle\Entity\Conditions $conditions
     *
     * @return User_Conditions
     */
    public function setConditions(\AppBundle\Entity\Conditions $conditions = null)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Get conditions
     *
     * @return \AppBundle\Entity\Conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }
}
