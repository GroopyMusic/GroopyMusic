<?php
// src/AppBundle/Form/RegistrationType.php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('username')
            // Duplicated from ProfileType
            ->add('lastname', TextType::class, array(
                'label' => 'labels.user.lastname',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'user.lastname.blank']),
                    new Length(['max' => 64, 'maxMessage' => 'user.lastname.long']),
                ]
            ))
            ->add('firstname', TextType::class, array(
                'label' => 'labels.user.firstname',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'user.firstname.blank']),
                    new Length(['max' => 64, 'maxMessage' => 'user.firstname.long']),
                ]
            ))
            // end duplicated
            ->add('accept_conditions', CheckboxType::class, array(
                'label' => 'labels.user.accept_conditions',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'user.accept_conditions.blank']),
                ],
            ))
            ->add('newsletter', CheckboxType::class, array(
                'label' => 'labels.user.newsletter',
                'required' => false,
            ))
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

}