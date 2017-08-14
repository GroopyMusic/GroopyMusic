<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ContractArtistType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contract = $builder->getData();
        $step = $contract->getStep();

        $builder
            ->add('motivations', TextareaType::class)
            ->add('preferences', ContractArtistPreferencesType::class, array(
                'step' => $step,
            ))
            ->add('accept_conditions', CheckboxType::class, array('required' => true))
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_contractartist';
    }


}
