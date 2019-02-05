<?php

namespace AppBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Genre;
use AppBundle\Entity\InformationSession;
use AppBundle\Entity\Province;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'label' => 'labels.artist.artistname',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 67, 'maxMessage' => 'artist.artistname.long'])
                ]
            ))
            ->add('province', EntityType::class, array(
                'label' => 'labels.artist.province',
                'class' => Province::class,
            ))
            ->add('phone', TextType::class, array(
                'required' => false,
                'label' => 'labels.artist.phone',
                'constraints' => new Length(['max' => 63]),
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
            ->add('website', UrlType::class, array(
                'label' => 'labels.artist.website',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('facebook', UrlType::class, array(
                'label' => 'labels.artist.facebook',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('twitter', UrlType::class, array(
                'label' => 'labels.artist.twitter',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('spotify', UrlType::class, array(
                'label' => 'labels.artist.spotify',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('soundcloud', UrlType::class, array(
                'label' => 'labels.artist.soundcloud',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('bandcamp', UrlType::class, array(
                'label' => 'labels.artist.bandcamp',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
               'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('instagram', UrlType::class, array(
                'label' => 'labels.artist.instagram',
                'required' => false,
                'constraints' => [
                    new Url(['message' => "artist.links.url"]),
                ],
                'attr' => ['placeholder' => 'http(s)://...'],
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.artist.submit',
                'attr' => ['class' => 'btn btn-primary']
            ))
        ;
        if(!$options['edit']) {
            if($options['iss']) {
                $builder->add('informationSession', EntityType::class, array(
                    'required' => true,
                    'label' => 'Session d\'information',
                    'class' => InformationSession::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->queryBuilderVisible();
                    },
                    'choice_label' => 'nameWithDate' ,
                ))
                ;
            }
            $builder->add('accept_conditions', CheckboxType::class, array(
                'required' => true,
                'label' => 'labels.artist.accept_conditions',
                'constraints' => [
                    new NotBlank(['message' => 'artist.accept_conditions.blank']),
                ],
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist::class,
            'edit' => false,
            'iss' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_artist';
    }
}
