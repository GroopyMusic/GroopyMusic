<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistOwnershipsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ownership_requests_form', CollectionType::class, array(
            'entry_type' => OwnerShipRequestType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'attr' => ['class' => 'ownership collection'],
            'label' => false,
        ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.artist_ownerships.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist::class,
        ));
    }

    public function getName()
    {
        return 'app_bundle_artist_ownerships_type';
    }
}
