<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChoiceOption
 *
 * @ORM\Table(name="choice_option")
 * @ORM\Entity(repositoryClass="XBundle\Repository\ChoiceOptionRepository")
 */
class ChoiceOption
{

    public function __toString()
    {
        return '' . $this->getName();
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
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\OptionProduct", inversedBy="choices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $option;


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
     * @return ChoiceOption
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
     * Set option
     *
     * @param \XBundle\Entity\OptionProduct $option
     *
     * @return ChoiceOption
     */
    public function setOption(\XBundle\Entity\OptionProduct $option = null)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option
     *
     * @return \XBundle\Entity\OptionProduct
     */
    public function getOption()
    {
        return $this->option;
    }

}
