<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Hall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtistValidationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Admin only so no translation needed
        $builder
            ->add('acceptConditions', CheckboxType::class, array(
                'required' => true,
                'label' => "J'ai bien relu les infos avant de valider.",
            ))
            ->add('marksuccessful', SubmitType::class, array(
                'label' => 'Marquer comme réussi',
                'attr' => array('class' => 'btn btn-success')
            ))
            ->add('markfailed', SubmitType::class, array(
                'label' => 'Marquer comme raté',
                'attr' => array('class' => 'btn btn-danger')
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_contractartist_validation';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContractArtist::class,
        ));
    }

}
