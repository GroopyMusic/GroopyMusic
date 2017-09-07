<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * Step
 *
 * @ORM\Table(name="step")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepRepository")
 */
class Step implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch(\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
    }

    public function __construct()
    {
        $this->counterParts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deadline_duration = 30;
    }

    public function getDefaultLocale() {
        return 'fr';
    }

    public function __toString()
    {
        return '' . $this->getName();
    }

    public function getAvailableDates(Province $province) {
        $dates = array();

        foreach($this->getHalls() as $hall) {
            if($province == $hall->getProvince())
                $dates = array_merge($dates, $hall->getAvailableDates());
        }

        return array_unique($dates);
    }

    public function getAvailableDatesFormatted(Province $province) {
        $availableDates = $this->getAvailableDates($province);

        $display = '';
        $count = count($availableDates);
        for($i = 0; $i < $count; $i++) {
            $display .= $availableDates[$i];
            if($i != $count - 1) {
                $display .= ',';
            }
        }
        return $display ;
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
     * @var int
     *
     * @ORM\Column(name="num", type="smallint")
     */
    private $num;

    /**
     * @ORM\ManyToOne(targetEntity="Phase", inversedBy="steps")
     */
    private $phase;

    /**
     * @ORM\ManyToOne(targetEntity="StepType", inversedBy="steps")
     */
    private $type;

    /**
     * @ORM\Column(name="deadline_duration", type="smallint")
     */
    private $deadline_duration;

    /**
     * @ORM\OneToMany(targetEntity="CounterPart", mappedBy="step")
     */
    private $counterParts;

    /**
     * @ORM\Column(name="required_amount", type="integer")
     */
    private $requiredAmount;

    /**
     * @ORM\OneToMany(targetEntity="Hall", mappedBy="step")
     */
    private $halls;

    /**
     * @ORM\Column(name="approximate_capacity", type="smallint")
     */
    private $approximate_capacity;

    /**
     * @ORM\Column(name="delay", type="smallint")
     */
    private $delay;

    /**
     * @ORM\Column(name="delay_margin", type="smallint")
     */
    private $delay_margin;

    /**
     * @ORM\Column(name="min_tickets", type="smallint")
     */
    private $min_tickets;

    /**
     * @ORM\Column(name="max_tickets", type="smallint")
     */
    private $max_tickets;

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
     * Set num
     *
     * @param integer $num
     *
     * @return Step
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set phase
     *
     * @param \AppBundle\Entity\Phase $phase
     *
     * @return Step
     */
    public function setPhase(\AppBundle\Entity\Phase $phase = null)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return \AppBundle\Entity\Phase
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\StepType $type
     *
     * @return Step
     */
    public function setType(\AppBundle\Entity\StepType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\StepType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set deadlineDuration
     *
     * @param integer $deadlineDuration
     *
     * @return Step
     */
    public function setDeadlineDuration($deadlineDuration)
    {
        $this->deadline_duration = $deadlineDuration;

        return $this;
    }

    /**
     * Get deadlineDuration
     *
     * @return integer
     */
    public function getDeadlineDuration()
    {
        return $this->deadline_duration;
    }

    /**
     * Add counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     *
     * @return Step
     */
    public function addCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counterParts[] = $counterPart;
        $counterPart->setStep($this);

        return $this;
    }

    /**
     * Remove counterPart
     *
     * @param \AppBundle\Entity\CounterPart $counterPart
     */
    public function removeCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counterParts->removeElement($counterPart);
    }

    /**
     * Get counterParts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCounterParts()
    {
        return $this->counterParts;
    }

    /**
     * Set requiredAmount
     *
     * @param integer $requiredAmount
     *
     * @return Step
     */
    public function setRequiredAmount($requiredAmount)
    {
        $this->requiredAmount = $requiredAmount;

        return $this;
    }

    /**
     * Get requiredAmount
     *
     * @return integer
     */
    public function getRequiredAmount()
    {
        return $this->requiredAmount;
    }

    /**
     * Add hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return Step
     */
    public function addHall(\AppBundle\Entity\Hall $hall)
    {
        $this->halls[] = $hall;

        return $this;
    }

    /**
     * Remove hall
     *
     * @param \AppBundle\Entity\Hall $hall
     */
    public function removeHall(\AppBundle\Entity\Hall $hall)
    {
        $this->halls->removeElement($hall);
    }

    /**
     * Get halls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHalls()
    {
        return $this->halls;
    }
}
