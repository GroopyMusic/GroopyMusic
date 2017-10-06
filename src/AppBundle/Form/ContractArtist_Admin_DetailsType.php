<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContractArtist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtist_Admin_DetailsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contract = $builder->getData();
        $step = $contract->getStep();

        $builder
            ->add('reality', ConcertPossibilityType::class, array(
                'required' => false,
                'step' => $step,
            ))
            ->add('coartists_list', CollectionType::class, array(
                'required' => false,
                'entry_type' => ContractArtist_ArtistType::class,
                'entry_options' => array(
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
                'label' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContractArtist::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_admin_contractartist_details';
    }
}