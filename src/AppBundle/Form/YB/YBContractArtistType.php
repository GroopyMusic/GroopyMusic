<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Form\CounterPartType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YBContractArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('noThreshold', CheckboxType::class, array(
                'label' => "N'a pas de seuil de validation",
                'required' => false,
            ))
            ->add('threshold', IntegerType::class, array(
                'required' => false,
                'label' => 'Seuil de validation',

            ))
            ->add('dateEnd', DateTimeType::class, array(
                'required' => false,
                'label' => 'Date de validation',
            ))
            ->add('dateClosure', DateTimeType::class, array(
                'required' => true,
                'label' => 'Fin des ventes',
            ))
            ->add('dateEvent', DateTimeType::class, array(
                'required' => false,
                'label' => "Date de l'événement (si applicable)"
            ))
            ->add('translations', TranslationsType::class, [
                'locales' => ['fr'],
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => 'Titre',
                    ],
                    'description' => [
                        'field_type' => TextareaType::class,
                        'label' => 'Description',
                    ],
                ],
                'exclude_fields' => ['additional_info', 'slug']
            ])
            ->add('counterParts',  CollectionType::class, array(
                'label' => 'Articles/Tickets en vente',
                'entry_type' => CounterPartType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YBContractArtist::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_type';
    }
}
