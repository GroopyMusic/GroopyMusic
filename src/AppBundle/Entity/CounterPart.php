<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CounterPart
 *
 * @ORM\Table(name="counter_part")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CounterPartRepository")
 */
class CounterPart implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __construct()
    {
        $this->festivaldays = new \Doctrine\Common\Collections\ArrayCollection();
        $this->free_price = false;
        $this->minimum_price = 0;
        $this->threshold_increase = 1;
        $this->maximum_amount_per_purchase = 1000;
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

    public function getDefaultLocale()
    {
        return 'fr';
    }

    public function __toString()
    {
        return '' . $this->getName();
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

    // Unmapped, memoized
    private $potential_artists = null;

    public function getPotentialArtists()
    {
        if ($this->potential_artists == null) {
            $artists = [];

            foreach ($this->festivaldays as $festivalday) {
                foreach ($festivalday->getPerformances() as $performance) {
                    $artists[] = $performance->getArtist();
                }
            }
            $this->potential_artists = array_unique($artists);
        }
        return $this->potential_artists;
    }

    public function getSemanticPrice()
    {
        if ($this->free_price) {
            return $this->getMinimumPrice();
        } else {
            return $this->getPrice();
        }
    }

    public function isFree()
    {
        return (!$this->free_price && $this->price == 0);
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
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="BaseStep", inversedBy="counterParts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $step;

    /**
     * @ORM\ManyToOne(targetEntity="BaseContractArtist", inversedBy="counterParts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contractArtist;

    /**
     * @ORM\Column(name="maximum_amount", type="smallint")
     */
    private $maximum_amount;

    /**
     * @ORM\Column(name="free_price", type="boolean")
     */
    private $free_price;

    /**
     * @ORM\Column(name="minimum_price", type="float")
     */
    private $minimum_price;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\FestivalDay", inversedBy="counterparts")
     */
    private $festivaldays;

    /**
     * @ORM\Column(name="threshold_increase", type="float")
     */
    private $threshold_increase;

    /**
     * @ORM\Column(name="is_child_entry", type="boolean")
     */
    private $is_child_entry;

    /**
     * @var int
     * @ORM\Column(name="maximum_amount_per_purchase", type="smallint", )
     */
    private $maximum_amount_per_purchase;

    /**
     * @ORM\Column(name="disabled", type="boolean")
     */
    private $disabled;

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
     * Set price
     *
     * @param float $price
     *
     * @return CounterPart
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return CounterPart
     */
    public function setStep(\AppBundle\Entity\Step $step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\Step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set maximumAmount
     *
     * @param integer $maximumAmount
     *
     * @return CounterPart
     */
    public function setMaximumAmount($maximumAmount)
    {
        $this->maximum_amount = $maximumAmount;

        return $this;
    }

    /**
     * Get maximumAmount
     *
     * @return integer
     */
    public function getMaximumAmount()
    {
        return $this->maximum_amount;
    }

    /**
     * @return mixed
     */
    public function getFreePrice()
    {
        return $this->free_price;
    }

    /**
     * @param mixed $free_price
     */
    public function setFreePrice($free_price)
    {
        $this->free_price = $free_price;
    }

    /**
     * @return mixed
     */
    public function getMinimumPrice()
    {
        return $this->minimum_price;
    }

    /**
     * @param mixed $minimum_price
     */
    public function setMinimumPrice($minimum_price)
    {
        $this->minimum_price = $minimum_price;
    }

    /**
     * @return mixed
     */
    public function getContractArtist()
    {
        return $this->contractArtist;
    }

    /**
     * @param mixed $contractArtist
     */
    public function setContractArtist($contractArtist)
    {
        $this->contractArtist = $contractArtist;
    }

    /**
     * Add festivalday
     *
     * @param \AppBundle\Entity\FestivalDay $festivalday
     *
     * @return CounterPart
     */
    public function addFestivalday(\AppBundle\Entity\FestivalDay $festivalday)
    {
        $this->festivaldays[] = $festivalday;

        return $this;
    }

    /**
     * Remove festivalday
     *
     * @param \AppBundle\Entity\FestivalDay $festivalday
     */
    public function removeFestivalday(\AppBundle\Entity\FestivalDay $festivalday)
    {
        $this->festivaldays->removeElement($festivalday);
    }

    /**
     * Get festivaldays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFestivaldays()
    {
        return $this->festivaldays;
    }

    /**
     * Set thresholdIncrease
     *
     * @param float $thresholdIncrease
     *
     * @return CounterPart
     */
    public function setThresholdIncrease($thresholdIncrease)
    {
        $this->threshold_increase = $thresholdIncrease;

        return $this;
    }

    /**
     * Get thresholdIncrease
     *
     * @return float
     */
    public function getThresholdIncrease()
    {
        return $this->threshold_increase;
    }

    /**
     * Set isChildEntry
     *
     * @param boolean $isChildEntry
     *
     * @return CounterPart
     */
    public function setIsChildEntry($isChildEntry)
    {
        $this->is_child_entry = $isChildEntry;

        return $this;
    }

    /**
     * Get isChildEntry
     *
     * @return boolean
     */
    public function getIsChildEntry()
    {
        return $this->is_child_entry;
    }

    /**
     * @return int
     */
    public function getMaximumAmountPerPurchase()
    {
        return $this->maximum_amount_per_purchase;
    }

    /**
     * @param int $maximum_amount_per_purchase
     */
    public function setMaximumAmountPerPurchase($maximum_amount_per_purchase)
    {
        $this->maximum_amount_per_purchase = $maximum_amount_per_purchase;
    }

    /**
     * @return boolean
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }
}
