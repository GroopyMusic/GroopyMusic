<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistMediasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('videos', CollectionType::class, array(
                'label' => false,
                'entry_type' => VideoType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'Enregistrer les modifications'
            ))
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
        return 'app_artist_medias';
    }
}
