<?php

namespace AppBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Genre;
use AppBundle\Entity\Province;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artistname', TextType::class, array(
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner le nom de l\'artiste.']),
                    new Length(['max' => 67, 'maxMessage' => 'Le nom d\'artiste ne peut excéder {{ limit }} caractères.'])
                ]
            ))
            ->add('province', EntityType::class, array(
                'class' => Province::class,
            ))
            ->add('translations', TranslationsType::class, array(
                // TODO
            ))
            ->add('genres', Select2EntityType::class, [
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => Genre::class,
                'primary_key' => 'id',
            ])
            ->add('videos', CollectionType::class, array(
                'entry_type' => VideoType::class,
                'entry_options' => array(
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
                'label' => false,
            ))
            ->add('website', UrlType::class, array(
                'required' => false,
                'constraints' => [
                    new Url(['message' => "Veuillez entrer une URL valide."]),
                ]
            ))
            ->add('facebook', UrlType::class, array(
                'required' => false,
                'constraints' => [
                    new Url(['message' => "Veuillez entrer une URL valide."]),
                ]
            ))
            ->add('twitter', UrlType::class, array(
                'required' => false,
                'constraints' => [
                    new Url(['message' => "Veuillez entrer une URL valide."]),
                ]
            ))
            ->add('spotify', UrlType::class, array(
                'required' => false,
                'constraints' => [
                    new Url(['message' => "Veuillez entrer une URL valide."]),
                ]
            ))
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist::class,
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_artist';
    }
}
