<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('username')
            ->remove('email')
            // Duplicated from RegistrationType
            ->add('lastname', TextType::class, array(
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un nom de famille.']),
                    new Length(['max' => 64, 'maxMessage' => 'Le nom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            ->add('firstname', TextType::class, array(
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un prénom.']),
                    new Length(['max' => 64, 'maxMessage' => 'Le prénom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            // End duplicated
            ->add('newsletter', CheckboxType::class, array(
                'label' => 'labels.user.newsletter',
                'required' => false,
            ))
            ->add('genres', Select2EntityType::class, [
                'label' => 'labels.user.genres',
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => 'AppBundle\Entity\Genre',
                'primary_key' => 'id',
            ])
            ->remove('current_password')
            ->add('addressForm', CollectionType::class, array(
                'entry_type' => AddressType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
                'label' => 'labels.user.address',
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.user.profile.submit'
            ))
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';

    }

    public function getBlockPrefix()
    {
        return 'app_user_profile';
    }
}