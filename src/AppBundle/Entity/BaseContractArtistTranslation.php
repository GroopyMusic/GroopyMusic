<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BaseContractArtistTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(name="additional_info", type="text", nullable=true)
     */
    protected $additional_info;

    /**
     * Set additionalInfo
     *
     * @param string $additionalInfo
     *
     * @return BaseContractArtist
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additional_info = $additionalInfo;

        return $this->translatable;
    }

    /**
     * Get additionalInfo
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }
}