<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 11:38
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Level
 *
 * @ORM\Table(name="level")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LevelRepository")
 */
class Level implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->statistics = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
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
        return '' . $this->getName();
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

    public function setStatistics($statistics)
    {
        $this->statistics = $statistics;
        return $this;
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
     * @ORM\Column(name="step", type="integer")
     */
    private $step;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="levels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="User_Category", mappedBy="level", cascade={"all"}, orphanRemoval=true)
     */
    private $statistics;

    /**
     * @ORM\ManyToMany(targetEntity="Reward", cascade={"all"})
     */
    private $rewards;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set step
     *
     * @param integer $step
     *
     * @return Level
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return integer
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Level
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add statistic
     *
     * @param \AppBundle\Entity\User_Category $statistic
     *
     * @return Level
     */
    public function addStatistic(User_Category $statistic)
    {
        $this->statistics[] = $statistic;

        return $this;
    }

    /**
     * Remove statistic
     *
     * @param \AppBundle\Entity\User_Category $statistic
     */
    public function removeStatistic(User_Category $statistic)
    {
        $this->statistics->removeElement($statistic);
    }

    /**
     * Get statistics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatistics()
    {
        return $this->statistics;
    }


    /**
     * Add reward
     *
     * @param \AppBundle\Entity\Reward $reward
     *
     * @return Level
     */
    public function addReward(Reward $reward)
    {
        $this->rewards[] = $reward;

        return $this;
    }

    /**
     * Remove reward
     *
     * @param \AppBundle\Entity\Reward $reward
     */
    public function removeReward(Reward $reward)
    {
        $this->rewards->removeElement($reward);
    }

    /**
     * Get rewards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRewards()
    {
        return $this->rewards;
    }
}
