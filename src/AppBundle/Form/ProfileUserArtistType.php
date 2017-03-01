<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUserArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artistname')
        ;
    }

    public function getParent()
    {
        return 'AppBundle\Form\ProfileType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_artist_profile';
    }
}
