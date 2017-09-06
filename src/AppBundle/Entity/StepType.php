<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * StepType
 *
 * @ORM\Table(name="step_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepTypeRepository")
 */
class StepType implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    const TYPE_CONCERT = 'Concert';

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
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
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="type", cascade={"persist"})
     */
     private $steps;


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
     * Constructor
     */
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return StepType
     */
    public function addStep(\AppBundle\Entity\Step $step)
    {
        $this->steps[] = $step;
        $step->setType($this);

        return $this;
    }

    /**
     * Remove step
     *
     * @param \AppBundle\Entity\Step $step
     */
    public function removeStep(\AppBundle\Entity\Step $step)
    {
        $this->steps->removeElement($step);
    }

    /**
     * Get steps
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
