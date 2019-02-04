<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Table(name="topping_string")
* @ORM\Entity(repositoryClass="AppBundle\Repository\ToppingStringRepository")
*/
class ToppingString extends Topping
{
    public function __construct($content)
    {
        $this->type = 'string';
        $this->barCodeText = 'ST' . uniqid();
        $this->setContent($content);
    }

    public function __toString()
    {
        return '' . $this->getContent();
    }

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
    }
}