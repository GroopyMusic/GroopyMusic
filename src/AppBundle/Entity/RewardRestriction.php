<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 23/03/2018
 * Time: 15:16
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * RewardRestriction
 *
 * @ORM\Table(name="reward_restriction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RewardRestrictionRepository")
 */
class RewardRestriction implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * Constructor
     */
    public function __construct()
    {
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
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="querry_name", type="text")
     */
    private $querry_name;

    /**
     * @ORM\ManyToOne(targetEntity="Reward", inversedBy="restrictions")
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
     * Set querryName
     *
     * @param string $querryName
     *
     * @return RewardRestriction
     */
    public function setQuerryName($querryName)
    {
        $this->querry_name = $querryName;

        return $this;
    }

    /**
     * Get querryName
     *
     * @return string
     */
    public function getQuerryName()
    {
        return $this->querry_name;
    }

    /**
     * Set reward
     *
     * @param \AppBundle\Entity\Reward $reward
     *
     * @return RewardRestriction
     */
    public function setReward(\AppBundle\Entity\Reward $reward = null)
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
