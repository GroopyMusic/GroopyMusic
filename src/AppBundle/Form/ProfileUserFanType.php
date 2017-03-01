<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileUserFanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    public function getParent()
    {
        return 'AppBundle\Form\ProfileType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_fan_profile';
    }
}