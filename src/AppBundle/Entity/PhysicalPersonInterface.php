<?php

namespace AppBundle\Entity;


interface PhysicalPersonInterface
{
    /**
     * @return string
     */
    public function getDisplayName();

    /**
     * @return string
     */
    public function getEmail();
}