<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class ToppingStringTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @var string
     * @ORM\Column(name="content", type="string")
     */
    private $content;

    public function getContent() {
        return $this->content;
    }

    /**
     * @param $content
     * @return ToppingString
     */
    public function setContent($content) {
        $this->content = $content;
        return $this->getTranslatable();
    }
}