<?php

namespace AppBundle\Services;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class BySlugNamer implements NamerInterface
{
    public function name($object, PropertyMapping $mapping):string {
        return $object->getUploadFileName();
    }
}