<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 15:46
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User_Reward
 *
 * @ORM\Table(name="fos_user__reward")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User_RewardRepository")
 */
class User_Reward
{
    public function __construct()
    {
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
     * @var int
     *
     * @ORM\Column(name="reduction", type="integer")
     */
    private $reduction;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creation_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="limit_date", type="datetime")
     */
    private $limit_date;

    /**
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(name="reward_type_parameters", type="array")
     */
    private $reward_type_parameters;

    /**
     * @ORM\ManyToOne(targetEntity="BaseContractArtist")
     */
    private $base_contract_artist;

    /**
     * @ORM\ManyToOne(targetEntity="BaseStep")
     */
    private $base_step;

    /**
     * @ORM\ManyToOne(targetEntity="CounterPart")
     */
    private $counter_part;

    /**
     * @ORM\ManyToOne(targetEntity="Artist")
     */
    private $artist;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="rewards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Reward", inversedBy="user_rewards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reward;

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
     * Set reduction
     *
     * @param integer $reduction
     *
     * @return User_Reward
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

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     *
     * @return User_Reward
     */
    public function setCreationDate($creationDate)
    {
        $this->creation_date = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * Set limitDate
     *
     * @param \DateTime $limitDate
     *
     * @return User_Reward
     */
    public function setLimitDate($limitDate)
    {
        $this->limit_date = $limitDate;

        return $this;
    }

    /**
     * Get limitDate
     *
     * @return \DateTime
     */
    public function getLimitDate()
    {
        return $this->limit_date;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return User_Reward
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set rewardTypeParameters
     *
     * @param array $rewardTypeParameters
     *
     * @return User_Reward
     */
    public function setRewardTypeParameters($rewardTypeParameters)
    {
        $this->reward_type_parameters = $rewardTypeParameters;

        return $this;
    }

    /**
     * Get rewardTypeParameters
     *
     * @return array
     */
    public function getRewardTypeParameters()
    {
        return $this->reward_type_parameters;
    }

    /**
     * Set baseContractArtist
     *
     * @param \AppBundle\Entity\BaseContractArtist $baseContractArtist
     *
     * @return User_Reward
     */
    public function setBaseContractArtist(BaseContractArtist $baseContractArtist = null)
    {
        $this->base_contract_artist = $baseContractArtist;

        return $this;
    }

    /**
     * Get baseContractArtist
     *
     * @return \AppBundle\Entity\BaseContractArtist
     */
    public function getBaseContractArtist()
    {
        return $this->base_contract_artist;
    }

    /**
     * Set baseStep
     *
     * @param \AppBundle\Entity\BaseStep $baseStep
     *
     * @return User_Reward
     */
    public function setBaseStep(BaseStep $baseStep = null)
    {
        $this->base_step = $baseStep;

        return $this;
    }

    /**
     * Get baseStep
     *
     * @return \AppBundle\Entity\BaseStep
     */
    public function getBaseStep()
    {
        return $this->base_step;
    }

    /**
     * Set counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return User_Reward
     */
    public function setCounterPart(CounterPart $counterPart = null)
    {
        $this->counter_part = $counterPart;

        return $this;
    }

    /**
     * Get counterPart
     *
     * @return \AppBundle\Entity\CounterPart
     */
    public function getCounterPart()
    {
        return $this->counter_part;
    }

    /**
     * Set artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return User_Reward
     */
    public function setArtist(Artist $artist = null)
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return User_Reward
     */
    public function setUser(User $user)
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
     * Set reward
     *
     * @param \AppBundle\Entity\Reward $reward
     *
     * @return User_Reward
     */
    public function setReward(Reward $reward)
    {
        $this->reward = $reward;

        return $this;
    }

    /**
     * Get reward
     *
     * @return \AppBundle\Entity\Reward
     */
    public function getReward()
    {
        return $this->reward;
    }
}
