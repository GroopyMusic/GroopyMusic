<?php

namespace AppBundle\Form;

use AppBundle\Entity\ConcertPossibility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcertPossibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => ['class' => 'datePicker', 'available-dates' => $options['available-dates']],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'available-dates' => [],
            'data_class' => ConcertPossibility::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_concertpossibility';
    }

    public function getParent(){
        return ContractArtistPossibilityType::class;
    }
}
