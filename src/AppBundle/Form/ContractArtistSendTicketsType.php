<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtistSendTicketsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Admin only so no translation needed
        $builder
            ->add('send', SubmitType::class, array(
                'label' => 'Envoyer les tickets',
                'attr' => array('class' => 'btn btn-success')
            ))
            ->add('preview', SubmitType::class, array(
                'label' => 'Prévisualiser les tickets',
                'attr' => array('class' => 'btn btn-outline-secondary')
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
        return 'app_bundle_contract_artist_send_tickets_type';
    }
}
