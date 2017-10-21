<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Province;
use AppBundle\Entity\Step;
use AppBundle\Entity\StepType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractArtistType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add('artist', EntityType::class, array(
                        'class' => Artist::class,
                        'query_builder' => function(EntityRepository $er) use ($options) {
                            return $er->queryNotCurrentlyBusy($options['user']);
                        }
                    ))
                    ->add('province', EntityType::class, array(
                        'required' => false,
                        'class' => Province::class,
                        'placeholder' => 'Sans importance',
                    ))
                    ->add('step', EntityType::class, array(
                        'class' => Step::class,
                    ))
                ;
                break;
            case 2:
                $builder
                    ->add('preferences', ConcertPossibilityType::class, array(
                        'required' => true,
                        'available-dates' => $options['available-dates'],
                    ))
                    ->add('motivations', TextareaType::class, array(
                        'required' => false,
                    ))
                ;
                break;
            case 3:
                $builder
                    ->add('accept_conditions', CheckboxType::class, array(
                        'required' => true
                    ))
                ;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_contractartist';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
            'available-dates' => null,
            'data_class' => ContractArtist::class,
        ));
    }


}
