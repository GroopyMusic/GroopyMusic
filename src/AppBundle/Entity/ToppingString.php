<?php

namespace AppBundle\Entity;

use Sonata\TranslationBundle\Model\TranslatableInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
* @ORM\Table(name="topping_string")
* @ORM\Entity(repositoryClass="AppBundle\Repository\ToppingStringRepository")
*/
class ToppingString extends Topping implements TranslatableInterface
{
    use ORMBehaviors\Translatable\Translatable;

    public function __construct()
    {
        $this->type = 'string';
        $this->barCodeText = 'ST' . uniqid();
    }

    public function __toString()
    {
        return '' . $this->getContent();
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

    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);
        return $this;
    }

    public function getLocale()
    {
        return $this->getCurrentLocale();
    }

}