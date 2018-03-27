<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 11:56
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Reward
 *
 * @ORM\Table(name="reward")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"invitation_reward" = "InvitationReward", "consomable_reward" = "ConsomableReward", "reduction_reward" = "ReductionReward"})
 *
 */
abstract class Reward implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user_rewards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->restrictions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch (\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function __toString()
    {
        return ' ' . $this->getName();
    }

    public function getDefaultLocale()
    {
        return 'fr';
    }

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
    }

    public function getType()
    {
        if ($this instanceof InvitationReward) {
            $type = "Invitation";
        } elseif ($this instanceof ConsomableReward) {
            $type = "Consommation";
        } elseif ($this instanceof ReductionReward) {
            $type = "Réduction";
        } else {
            $type = explode('\\', get_class($this));
            $type = end($type);
        }
        return $type;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="max_use", type="integer")
     */
    protected $max_use;

    /**
     * @ORM\Column(name="validity_period", type="integer")
     */
    protected $validity_period;

    /**
     * @ORM\OneToMany(targetEntity="User_Reward", mappedBy="reward", cascade={"all"}, orphanRemoval=true)
     */
    protected $user_rewards;

    /**
     * @ORM\ManyToMany(targetEntity="RewardRestriction", mappedBy="rewards", cascade={"all"})
     */
    protected $restrictions;


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
     * Set maxUse
     *
     * @param boolean $maxUse
     *
     * @return Reward
     */
    public function setMaxUse($maxUse)
    {
        $this->max_use = $maxUse;

        return $this;
    }

    /**
     * Get maxUse
     *
     * @return boolean
     */
    public function getMaxUse()
    {
        return $this->max_use;
    }

    /**
     * Set validityPeriod
     *
     * @param integer $validityPeriod
     *
     * @return Reward
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validity_period = $validityPeriod;

        return $this;
    }

    /**
     * Get validityPeriod
     *
     * @return integer
     */
    public function getValidityPeriod()
    {
        return $this->validity_period;
    }


    /**
     * Add userReward
     *
     * @param \AppBundle\Entity\User_Reward $userReward
     *
     * @return Reward
     */
    public function addUserReward(User_Reward $userReward)
    {
        $this->user_rewards[] = $userReward;

        return $this;
    }

    /**
     * Remove userReward
     *
     * @param \AppBundle\Entity\User_Reward $userReward
     */
    public function removeUserReward(User_Reward $userReward)
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
     * Add restriction
     *
     * @param \AppBundle\Entity\RewardRestriction $restriction
     *
     * @return Reward
     */
    public function addRestriction(RewardRestriction $restriction)
    {
        $this->restrictions[] = $restriction;

        return $this;
    }

    /**
     * Remove restriction
     *
     * @param \AppBundle\Entity\RewardRestriction $restriction
     */
    public function removeRestriction(RewardRestriction $restriction)
    {
        $this->restrictions->removeElement($restriction);
    }

    /**
     * Get restrictions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestrictions()
    {
        return $this->restrictions;
    }
}
