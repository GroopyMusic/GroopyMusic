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

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="remain_use", type="boolean")
     */
    protected $remain_use;

    /**
     * @ORM\Column(name="validity_period", type="integer")
     */
    protected $validity_period;

    /**
     * @ORM\OneToMany(targetEntity="User_Reward", mappedBy="reward", cascade={"all"}, orphanRemoval=true)
     */
    private $user_rewards;

    /**
     * @ORM\OneToMany(targetEntity="RewardRestriction", mappedBy="reward", cascade={"all"})
     */
    private $restrictions;

    /**
     * @ORM\ManyToMany(targetEntity="Category", mappedBy="rewards", cascade={"all"})
     */
    private $categories;


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
     * Set remainUse
     *
     * @param boolean $remainUse
     *
     * @return Reward
     */
    public function setRemainUse($remainUse)
    {
        $this->remain_use = $remainUse;

        return $this;
    }

    /**
     * Get remainUse
     *
     * @return boolean
     */
    public function getRemainUse()
    {
        return $this->remain_use;
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

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Reward
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \AppBundle\Entity\Category $category
     */
    public function removeCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
