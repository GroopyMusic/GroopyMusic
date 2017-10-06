<?php

namespace AppBundle\Form;

use AppBundle\Entity\ArtistOwnershipRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class OwnerShipRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'required' => true,
                'constraints' => [
                    new Email(['message' => 'Cette adresse e-mail est invalide.']),
                ]
            ));
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
