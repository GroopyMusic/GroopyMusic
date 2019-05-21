<?php


namespace AppBundle\Entity\YB;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class OrganizationTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private $description;


    /**
     * Set description
     *
     * @param string $description
     *
     * @return OrganizationTranslation
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
}