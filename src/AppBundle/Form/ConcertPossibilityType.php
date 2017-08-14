<?php

namespace AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConcertPossibilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hall', EntityType::class, array(
                'class' => 'AppBundle\Entity\Hall',
                'choice_label' => 'Salle',
                'query_builder' => function (EntityRepository $er) use ($step) {
                    return $er->createQueryBuilder('h')
                        ->where('h.step = :step')
                        ->setParameter('step', $step)
                        ->orderBy('h.name', 'ASC');
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'step' => null,
            'data_class' => 'AppBundle\Entity\ConcertPossibility'
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
