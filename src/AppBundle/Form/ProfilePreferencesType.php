<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProfilePreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newsletter', CheckboxType::class, array(
                'required' => false,
                'label' => 'labels.user.newsletter',
            ))
            ->add('genres', Select2EntityType::class, [
                'required' => false,
                'label' => false,
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => 'AppBundle\Entity\Genre',
                'primary_key' => 'id',
            ])
            ->add('addressForm', CollectionType::class, array(
                'required' => false,
                'entry_type' => AddressType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
                'label' => false,
            ))
            ->add('birthday', BirthdayType::class, array(
                'label' => false,
                'required' => false,
                'years' => range(1920, date('Y')),
                'constraints' => [
                    new Date(['message' => 'constraints.user.preferences.birthday.date']),
                ]
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'labels.user.preferences.submit'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }

    public function getName()
    {
        return 'app_user_profile_preferences';
    }
}
