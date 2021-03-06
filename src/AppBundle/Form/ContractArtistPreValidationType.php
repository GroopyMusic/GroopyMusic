<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtistPreValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Admin only so no translation needed
        $builder
            ->add('marksuccessful', SubmitType::class, array(
                'label' => 'Marquer comme réussi',
                'attr' => array('class' => 'btn btn-success')
            ))
            ->add('markfailed', SubmitType::class, array(
                'label' => 'Marquer comme raté',
                'attr' => array('class' => 'btn btn-danger')
            ))
            ->add('cancel', SubmitType::class, array(
                'label' => 'Annuler',
                'attr' => array('class' => 'btn btn-secondary')
            ))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_contract_artist_pre_validation_type';
    }
}
