<?php

namespace AppBundle\Form;

use AppBundle\Entity\StepType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtistPreferencesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $step = $options['step'];

        $builder
            ->add('date')
            ->add('additionalInfo');

        if ($step != null && $step->getType()->getName() == StepType::TYPE_CONCERT) {
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
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'step'         => null,
            'data_class' => 'AppBundle\Entity\ContractArtistPreferences'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_contractartistpreferences';
    }

}
