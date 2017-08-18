<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Phase
 *
 * @ORM\Table(name="phase")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhaseRepository")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translations\PhaseTranslation")
 */
class Phase extends AbstractPersonalTranslatable implements TranslatableInterface
{
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return $this->num . ' ' . $this->name;
    }


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Translations\PhaseTranslation",
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
     * @var int
     *
     * @ORM\Column(name="num", type="smallint", unique=true)
     */
    private $num;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="phase", cascade={"persist"})
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
     * @return Phase
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
     * Set num
     *
     * @param integer $num
     *
     * @return Phase
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
     * Add step
     *
     * @param \AppBundle\Entity\Step $step
     *
     * @return Phase
     */
    public function addStep(\AppBundle\Entity\Step $step)
    {
        $this->steps[] = $step;
        $step->setPhase($this);

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
     * @param \AppBundle\Entity\Translations\PhaseTranslation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Translations\PhaseTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }
}
