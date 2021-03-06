<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 10:25
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user_statistics = new \Doctrine\Common\Collections\ArrayCollection();
        $this->levels = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="formula", type="string", length=255)
     */
    private $formula;

    /**
     * @ORM\OneToMany(targetEntity="User_Category", mappedBy="category", cascade={"all"}, orphanRemoval=true)
     */
    private $user_statistics;

    /**
     * @ORM\OneToMany(targetEntity="Level", mappedBy="category", cascade={"all"}, orphanRemoval=true)
     */
    private $levels;

    /**
     * @ORM\ManyToMany(targetEntity="Reward", cascade={"persist"})
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
     * Set formula
     *
     * @param string $formula
     *
     * @return Category
     */
    public function setFormula($formula)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Get formula
     *
     * @return string
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * Add userStatistic
     *
     * @param \AppBundle\Entity\User_Category $userStatistic
     *
     * @return Category
     */
    public function addUserStatistic(\AppBundle\Entity\User_Category $userStatistic)
    {
        if (!$this->user_statistics->contains($userStatistic)) {
            $userStatistic->setCategory($this);
            $this->user_statistics[] = $userStatistic;
        }
        return $this;
    }

    /**
     * Remove userStatistic
     *
     * @param \AppBundle\Entity\User_Category $userStatistic
     */
    public function removeUserStatistic(\AppBundle\Entity\User_Category $userStatistic)
    {
        if ($this->user_statistics->contains($userStatistic)) {
            $this->user_statistics->removeElement($userStatistic);
            $userStatistic->setCategory(null);
        }
    }

    /**
     * Get userStatistics
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserStatistics()
    {
        return $this->user_statistics;
    }

    /**
     * Add level
     *
     * @param \AppBundle\Entity\Level $level
     *
     * @return Category
     */
    public function addLevel(\AppBundle\Entity\Level $level)
    {
        if (!$this->levels->contains($level)) {
            $this->levels[] = $level;
            $level->setCategory($this);
        }
        return $this;
    }

    /**
     * Remove level
     *
     * @param \AppBundle\Entity\Level $level
     */
    public function removeLevel(\AppBundle\Entity\Level $level)
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
        }
    }

    /**
     * Get levels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * Add reward
     *
     * @param \AppBundle\Entity\Reward $reward
     *
     * @return Category
     */
    public function addReward(\AppBundle\Entity\Reward $reward)
    {
        $this->rewards[] = $reward;

        return $this;
    }

    /**
     * Remove reward
     *
     * @param \AppBundle\Entity\Reward $reward
     */
    public function removeReward(\AppBundle\Entity\Reward $reward)
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
