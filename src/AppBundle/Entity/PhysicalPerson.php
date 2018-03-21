<?php

namespace AppBundle\Entity;


class PhysicalPerson implements PhysicalPersonInterface
{
    public $firstname;
    public $lastname;
    public $email;

    public $other_names;

    public function __construct($firstname, $lastname, $email, $other_names = null)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->other_names = $other_names;
    }

    public function getDisplayName()
    {
        $str = $this->firstname . ' ' . $this->lastname;
        if($this->other_names != null) $str .= ' (' . $this->other_names . ')';
        return $str;
    }

    public function getEmail()
    {
        return $this->email;
    }

}