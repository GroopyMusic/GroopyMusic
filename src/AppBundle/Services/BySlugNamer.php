<?php
/**
 * Created by PhpStorm.
 * User: Gonzague
 * Date: 23-11-17
 * Time: 11:50
 */

namespace AppBundle\Services;

use AppBundle\Entity\Hall;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

class BySlugNamer implements NamerInterface
{
    public function name($object, PropertyMapping $mapping) {
        return $object->getUploadFileName();
    }
}