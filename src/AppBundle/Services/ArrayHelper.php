<?php

namespace AppBundle\Services;


class ArrayHelper
{
    /** Flattens a two+ dimensionnal array */
    public static function flattenArray($arrayToFlatten) {
        $flatArray = array();
        foreach($arrayToFlatten as $element) {
            if (is_array($element)) {
                $flatArray = array_merge($flatArray, self::flattenArray($element));
            } else {
                $flatArray[] = $element;
            }
        }
        return $flatArray;
    }
}