<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * StepType
 *
 * @ORM\Table(name="step_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StepTypeRepository")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translations\StepTypeTranslation")
 */
class StepType extends AbstractPersonalTranslatable implements TranslatableInterface
{
    const TYPE_CONCERT = 'Concert';

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Translations\StepTypeTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $translations;

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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Gedmo\Translatable
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Gedmo\Translatable
     */
    private $description;

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
     * Set name
     *
     * @param string $name
     *
     * @return StepType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return StepType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * Remove translation
     *
     * @param \AppBundle\Entity\Translations\StepTypeTranslation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Translations\StepTypeTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }
}
