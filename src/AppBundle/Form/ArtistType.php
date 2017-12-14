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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                'label' => 'labels.artist.artistname',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner le nom de l\'artiste.']),
                    new Length(['max' => 67, 'maxMessage' => 'Le nom d\'artiste ne peut excéder {{ limit }} caractères.'])
                ]
            ))
            ->add('province', EntityType::class, array(
                'label' => 'labels.artist.province',
                'class' => Province::class,
            ))
            ->add('translations', TranslationsType::class, array(
                'label' => $options['edit'] ? false : 'labels.artist.translations',
                'locales' => ['fr'],
                'fields' => [
                    'short_description' => [
                        'field_type' => TextareaType::class,
                        'label' => 'labels.artist.short_description',
                    ],
                    'biography' => [
                        'field_type' => TextareaType::class,
                        'label' => 'labels.artist.biography'
                    ]
                ]
            ))
            ->add('genres', Select2EntityType::class, [
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => Genre::class,
                'primary_key' => 'id',
                'label' => 'labels.artist.genres',
            ])
            ->add('website', TextType::class, array(
                'label' => 'labels.artist.website',
                'required' => false,
                //'constraints' => [
                //    new Url(['message' => "Veuillez entrer une URL valide."]),
                // ]
            ))
            ->add('facebook', TextType::class, array(
                'label' => 'labels.artist.facebook',
                'required' => false,
                // 'constraints' => [
                //    new Url(['message' => "Veuillez entrer une URL valide."]),
                // ]
            ))
            ->add('twitter', TextType::class, array(
                'label' => 'labels.artist.twitter',
                'required' => false,
                // 'constraints' => [
                //    new Url(['message' => "Veuillez entrer une URL valide."]),
                // ]
            ))
            ->add('spotify', TextType::class, array(
                'label' => 'labels.artist.spotify',
                'required' => false,
                //'constraints' => [
                //    new Url(['message' => "Veuillez entrer une URL valide."]),
                // ]
            ))
            ->add('soundcloud', TextType::class, array(
                'label' => 'labels.artist.soundcloud',
                'required' => false,
                // 'constraints' => [
                //     new Url(['message' => "Veuillez entrer une URL valide."]),
                // ]
            ))
            ->add('bandcamp', TextType::class, array(
                'label' => 'labels.artist.bandcamp',
                'required' => false,
                // 'constraints' => [
                //    new Url(['message' => "Veuillez entrer une URL valide."]),
                //]
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.artist.submit',
                'attr' => ['class' => 'btn btn-primary']
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist::class,
            'edit' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_artist';
    }
}
