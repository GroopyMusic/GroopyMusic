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
        $required = $options['required_reality'];

        $date_options = array(
            'required' => $required,
            'label' => 'labels.concertpossibility.date',
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => ['class' => 'datePicker', 'available-dates' => $options['available-dates']],
        );
        if($required) {
            $date_options['constraints'] = [
                new NotBlank(['message' => 'concert_possibility.date.blank']),
            ];
        }

        if($options['is_reality']) {
            $builder
                ->remove('additional_info')
                ->add('hall', null, array(
                    'label' => 'labels.concertpossibility.hall',
                    'required' => $required,
                ))
            ;
            if($options['date_value'] != null)
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
            'required_reality' => true,
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
