<?php

namespace AppBundle\Form;

use AppBundle\Entity\ArtistOwnershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OwnerShipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ArtistOwnershipRequest::class,
        ));
    }

    public function getName()
    {
        return 'app_bundle_owner_ship_request_type';
    }

    public function getBlockPrefix()
    {
        return 'OwnershipCollection';
    }
}
