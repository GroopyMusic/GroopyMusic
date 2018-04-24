<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 15:46
 */

namespace AppBundle\Entity;

use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * User_Reward
 *
 * @ORM\Table(name="fos_user__reward")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User_RewardRepository")
 */
class User_Reward
{
    public function __construct(Reward $reward, User $user)
    {
        $this->reward = $reward;
        $this->user = $user;
        $this->creation_date = new \DateTime();
        $this->limit_date = new \DateTime();
        $this->remain_use = $reward->getMaxUse();
        date_add($this->limit_date, new DateInterval('P' . $reward->getValidityPeriod() . 'D'));
        $this->active = true;
        $this->reward_type_parameters = $reward->getVariables();
        $this->artists = new ArrayCollection();
        $this->base_contract_artists = new ArrayCollection();
        $this->base_steps = new ArrayCollection();
        $this->counter_parts = new ArrayCollection();
        $this->contractFans = new ArrayCollection();
    }

    public function __toString()
    {
        return ' ' . $this->getReward()->getName();
    }

    public function displayPracticalInformation()
    {
        if ($this->reward instanceof ReductionReward) {
            return $this->getReward()->getName() . ': ' . $this->reward_type_parameters['reduction'] . '%';
        } else if ($this->reward instanceof InvitationReward) {
            return $this->getReward()->getName();
        } else if ($this->reward instanceof ConsomableReward) {
            return $this->getReward()->getName() . ': ' . $this->reward_type_parameters['quantity'] . ' x ' . $this->reward_type_parameters['type_consomable'] . '(' . $this->reward_type_parameters['value'] . ')';
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
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creation_date;

    /**
     * @var int
     *
     * @ORM\Column(name="remain_use", type="integer")
     */
    private $remain_use;

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
     * @ORM\ManyToMany(targetEntity="BaseContractArtist")
     */
    private $base_contract_artists;

    /**
     * @ORM\ManyToMany(targetEntity="BaseStep")
     */
    private $base_steps;

    /**
     * @ORM\ManyToMany(targetEntity="CounterPart")
     */
    private $counter_parts;

    /**
     * @ORM\ManyToMany(targetEntity="Artist")
     */
    private $artists;

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
     * @ORM\ManyToMany(targetEntity="ContractFan", mappedBy="user_rewards")
     */
    private $contractFans;

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


    /**
     * Add baseContractArtist
     *
     * @param \AppBundle\Entity\BaseContractArtist $baseContractArtist
     *
     * @return User_Reward
     */
    public function addBaseContractArtist(\AppBundle\Entity\BaseContractArtist $baseContractArtist)
    {
        $this->base_contract_artists[] = $baseContractArtist;

        return $this;
    }

    /**
     * Remove baseContractArtist
     *
     * @param \AppBundle\Entity\BaseContractArtist $baseContractArtist
     */
    public function removeBaseContractArtist(\AppBundle\Entity\BaseContractArtist $baseContractArtist)
    {
        $this->base_contract_artists->removeElement($baseContractArtist);
    }

    /**
     * Get baseContractArtists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBaseContractArtists()
    {
        return $this->base_contract_artists;
    }

    /**
     * Add baseStep
     *
     * @param \AppBundle\Entity\BaseStep $baseStep
     *
     * @return User_Reward
     */
    public function addBaseStep(\AppBundle\Entity\BaseStep $baseStep)
    {
        $this->base_steps[] = $baseStep;

        return $this;
    }

    /**
     * Remove baseStep
     *
     * @param \AppBundle\Entity\BaseStep $baseStep
     */
    public function removeBaseStep(\AppBundle\Entity\BaseStep $baseStep)
    {
        $this->base_steps->removeElement($baseStep);
    }

    /**
     * Get baseSteps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBaseSteps()
    {
        return $this->base_steps;
    }

    /**
     * Add counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return User_Reward
     */
    public function addCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counter_parts[] = $counterPart;

        return $this;
    }

    /**
     * Remove counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     */
    public function removeCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counter_parts->removeElement($counterPart);
    }

    /**
     * Get counterParts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounterParts()
    {
        return $this->counter_parts;
    }

    /**
     * Add artist
     *
     * @param \AppBundle\Entity\Artist $artist
     *
     * @return User_Reward
     */
    public function addArtist(\AppBundle\Entity\Artist $artist)
    {
        $this->artists[] = $artist;

        return $this;
    }

    /**
     * Remove artist
     *
     * @param \AppBundle\Entity\Artist $artist
     */
    public function removeArtist(\AppBundle\Entity\Artist $artist)
    {
        $this->artists->removeElement($artist);
    }

    /**
     * Get artists
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * Set remainUse
     *
     * @param integer $remainUse
     *
     * @return User_Reward
     */
    public function setRemainUse($remainUse)
    {
        $this->remain_use = $remainUse;

        return $this;
    }

    /**
     * Get remainUse
     *
     * @return integer
     */
    public function getRemainUse()
    {
        return $this->remain_use;
    }

    /**
     * Add contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     *
     * @return User_Reward
     */
    public function addContractFan(\AppBundle\Entity\ContractFan $contractFan)
    {
        $this->contractFans[] = $contractFan;

        return $this;
    }

    /**
     * Remove contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     */
    public function removeContractFan(\AppBundle\Entity\ContractFan $contractFan)
    {
        $this->contractFans->removeElement($contractFan);
    }

    /**
     * Get contractFans
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContractFans()
    {
        return $this->contractFans;
    }
}
