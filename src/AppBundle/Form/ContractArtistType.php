<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist;
use AppBundle\Entity\BaseContractArtist;
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
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContractArtistType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO ROLE_ADMIN
        $user = $options['user']->hasRole('ROLE_SUPER_ADMIN') ? null : $options['user'];

        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add('artist', EntityType::class, array(
                        'label' => 'labels.contractartist.artist',
                        'class' => Artist::class,
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->queryNotCurrentlyBusy($user);
                        },
                        'constraints' => [
                            new NotBlank(['message' => 'contractartist.artist.blank']),
                        ],
                    ))
                    ->add('province', EntityType::class, array(
                        'label' => 'labels.contractartist.province',
                        'required' => false,
                        'class' => Province::class,
                        'placeholder' => 'Sans importance', // TODO translate
                    ))
                    ->add('step', EntityType::class, array(
                        'label' => 'labels.contractartist.step',
                        'class' => Step::class,
                        'constraints' => [
                            new NotBlank(['message' => 'contractartist.artist.blank']),
                        ],
                    ));
                break;
            case 2:
                $builder
                    ->add('preferences', ConcertPossibilityType::class, array(
                        'label' => false,
                        'required' => true,
                        'available-dates' => $options['available-dates'],
                    ))
                    ->add('motivations', TextareaType::class, array(
                        'label' => 'labels.contractartist.motivations',
                        'required' => false,
                    ));
                break;
            case 3:
                if ($user == null) {
                    $builder
                        ->add('testPeriod', CheckboxType::class, array(
                            'required' => false,
                            'label' => 'Doit avoir une période de test',
                        ))
                        ->add('reality', ConcertPossibilityType::class, array(
                            'label' => 'Détails connus',
                            'required' => false,
                            'required_reality' => false,
                            'is_reality' => true,
                            'available-dates' => $options['available-dates'],
                            'date_value' => $builder->getData()->getPreferences()->getDate(),
                        ));
                }
                $builder->add('accept_conditions', CheckboxType::class, array(
                        'label' => 'labels.contractartist.accept_conditions',
                        'required' => true,
                        'constraints' => [
                            new IsTrue(['message' => 'contractartist.accept_conditions.blank']),
                        ],
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
