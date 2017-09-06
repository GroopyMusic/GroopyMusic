<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class ArtistTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(name="short_description", type="string", length=255)
     */
    private $short_description;

    /**
     * @ORM\Column(name="biography", type="text")
     */
    private $biography;


    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return ArtistTranslation
     */
    public function setShortDescription($shortDescription)
    {
        $this->short_description = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * Set biography
     *
     * @param string $biography
     *
     * @return ArtistTranslation
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * Get biography
     *
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }
}
