<?php

namespace AppBundle\Form;

use AppBundle\Entity\ConcertPossibility;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConcertPossibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date_options = array(
            'required' => true,
            'label' => 'labels.concertpossibility.date',
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => ['class' => 'datePicker', 'available-dates' => $options['available-dates']],
            'constraints' => [
                new NotBlank(['message' => 'Merci de renseigner une date pour le concert.']),
            ],
        );

        if($options['is_reality']) {
            $builder
                ->remove('additional_info')
                ->add('hall', null, array(
                    'label' => 'Salle où aura lieu le concert',
                    'required' => true,
                ))
            ;
            $date_options['data'] = $options['date_value'];
        }


        $builder
            ->add('date', DateType::class, $date_options);
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'available-dates' => '',
            'data_class' => ConcertPossibility::class,
            'is_reality' => false,
            'date_value' => null,
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
