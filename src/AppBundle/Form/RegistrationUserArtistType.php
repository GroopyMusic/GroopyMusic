<?php
// src/AppBundle/Form/RegistrationType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationUserArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artistname')
        ;
    }

    public function getParent()
    {
        return 'AppBundle\Form\RegistrationType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_artist_registration';
    }

}