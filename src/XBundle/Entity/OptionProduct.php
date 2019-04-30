<?php

namespace XBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * OptionProduct
 *
 * @ORM\Table(name="option_product")
 * @ORM\Entity(repositoryClass="XBundle\Repository\OptionProductRepository")
 */
class OptionProduct
{
    public function __construct() 
    {
        $this->choices = new ArrayCollection();
    }

    public function __toString()
    {
        $str = '' . $this->getName() . ' : ';

        $i = 0;
        foreach ($this->getChoices() as $choice) {
            $str .= $choice;
            if ($i < count($this->getChoices())-1) {
                $str .= ', ';
            }
            $i++;
        }

        return $str;
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Product", inversedBy="options")
     * @ORM\JoinColumn(nullable=true)
     */
    private $product;

    /**
     * @ORM\OneToMany(targetEntity="XBundle\Entity\ChoiceOption", mappedBy="option", cascade={"all"}, orphanRemoval=true)
     */
    private $choices;


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
     * @return OptionProduct
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
     * Set product
     *
     * @param \XBundle\Entity\Product $product
     *
     * @return OptionProduct
     */
    public function setProduct(\XBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \XBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Add choice
     *
     * @param \XBundle\Entity\ChoiceOption $choice
     *
     * @return OptionProduct
     */
    public function addChoice(\XBundle\Entity\ChoiceOption $choice)
    {
        $choice->setOption($this);
        $this->choices[] = $choice;
        return $this;
    }

    /**
     * Remove choice
     *
     * @param \XBundle\Entity\ChoiceOption $choice
     */
    public function removeChoice(\XBundle\Entity\ChoiceOption $choice)
    {
        $choice->setOption(null);
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }
}
