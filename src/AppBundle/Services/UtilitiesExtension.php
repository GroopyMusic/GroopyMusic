<?php

namespace AppBundle\Services;


class UtilitiesExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('decode_html', array($this, 'decode')),
        );
    }

    public function decode($value)
    {
        return html_entity_decode($value);
    }
}