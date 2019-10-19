<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Sonata\TranslationBundle\Model\TranslatableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StageRepository")
 * @ORM\Table(name="stage")
 **/
class Stage implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __construct()
    {
        $this->lineups = new ArrayCollection();
    }

    public function __call($method, $arguments)
    {
        try {
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        } catch(\Exception $e) {
            $method = 'get' . ucfirst($method);
            return $this->proxyCurrentLocaleTranslation($method, $arguments);
        }
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
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="LineUp", mappedBy="stage")
     */
    private $lineups; 

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
     * Add lineup
     *
     * @param \AppBundle\Entity\ContractArtist $lineup
     *
     * @return LineUpDay
     */
    public function addLineup(\AppBundle\Entity\ContractArtist $lineup)
    {
        $this->lineups[] = $lineup;
        $lineup->addLineUpday($this);

        return $this;
    }

    /**
     * Remove lineup
     *
     * @param \AppBundle\Entity\ContractArtist $lineup
     */
    public function removeLineup(\AppBundle\Entity\ContractArtist $lineup)
    {
        $this->lineups->removeElement($lineup);
        $lineup->removeLineUpday($this);
    }

    /**
     * Get lineups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLineups()
    {
        return $this->lineups;
    }
}