<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContractArtistPossibility;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcertPossibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $step = $options['step'];

        $builder
            /*->add('hall', EntityType::class, array(
                'class' => 'AppBundle\Entity\Hall',
                'query_builder' => function (EntityRepository $er) use ($step) {
                    return $er->createQueryBuilder('h')
                        ->where('h.step = :step')
                        ->setParameter('step', $step)
                        ->orderBy('h.name', 'ASC');
                }
            ))*/
            ->remove('date')
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => ['class' => 'datePicker', 'available-dates' => $step->getAvailableDatesFormatted()],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'step' => null,
            'data_class' => \AppBundle\Entity\ConcertPossibility::class,
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
