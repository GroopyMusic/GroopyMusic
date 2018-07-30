<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class ContractArtistSalesTranslation
{
    use ORMBehaviors\Translatable\Translation;
}
