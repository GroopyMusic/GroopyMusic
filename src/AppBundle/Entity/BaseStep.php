<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * @ORM\Table(name="base_step")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"concert" = "Step", "default" = "BaseStep"})
 */
class BaseStep implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    const TYPE_CONCERT = 'concert';

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
     * @var int
     *
     * @ORM\Column(name="num", type="smallint")
     */
    protected $num;

    /**
     * @ORM\ManyToOne(targetEntity="Phase", inversedBy="steps")
     */
    protected $phase;

    // Discriminator
    protected $type;

    /**
     * @ORM\Column(name="deadline_duration", type="smallint")
     */
    protected $deadline_duration;

    /**
     * @ORM\OneToMany(targetEntity="CounterPart", mappedBy="step")
     */
    protected $counterParts;

    /**
     * @ORM\Column(name="required_amount", type="integer", nullable=true)
     */
    protected $requiredAmount;

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
     * @return BaseStep
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
     * @return BaseStep
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
     * Set deadlineDuration
     *
     * @param integer $deadlineDuration
     *
     * @return BaseStep
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
     * @return BaseStep
     */
    public function addCounterPart(\AppBundle\Entity\CounterPart $counterPart)
    {
        $this->counterParts[] = $counterPart;
        $counterPart->setBaseStep($this);

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
     * @return BaseStep
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


}
