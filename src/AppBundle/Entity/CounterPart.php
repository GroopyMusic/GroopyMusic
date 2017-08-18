<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sonata\TranslationBundle\Model\Gedmo\AbstractPersonalTranslatable;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\TranslationBundle\Model\Gedmo\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CounterPart
 *
 * @ORM\Table(name="counter_part")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CounterPartRepository")
 * @Gedmo\TranslationEntity(class="AppBundle\Entity\Translations\CounterPartTranslation")
 */
class CounterPart extends AbstractPersonalTranslatable implements TranslatableInterface
{
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Translations\CounterPartTranslation",
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
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Gedmo\Translatable
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Step", inversedBy="counterParts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $step;

    /**
     * @ORM\Column(name="description", type="text")
     * @Gedmo\Translatable
     */
    private $description;

    /**
     * @ORM\Column(name="maximum_amount", type="smallint")
     */
    private $maximum_amount;

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
     * Set name
     *
     * @param string $name
     *
     * @return CounterPart
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
     * Set description
     *
     * @param string $description
     *
     * @return CounterPart
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
     * Remove translation
     *
     * @param \AppBundle\Entity\Translations\CounterPartTranslation $translation
     */
    public function removeTranslation(\AppBundle\Entity\Translations\CounterPartTranslation $translation)
    {
        $this->translations->removeElement($translation);
    }
}
