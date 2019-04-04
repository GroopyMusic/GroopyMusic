<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\VenueConfig;
use AppBundle\Form\PhotoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VenueConfigType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){
        if ($options['block']){
            $builder
                ->add('blocks', CollectionType::class, array(
                    'label' => 'Ajout de bloc',
                    'entry_type' => BlockType::class,
                    'entry_options' => array(
                        'label' => false,
                        'row' => false,
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'attr' => ['class' => 'second-collection'],
                ));
        } else {
            $builder
                ->add('name', TextType::class, array(
                    'required' => true,
                    'label' => "Nom de la configuration",
                ))
                ->add('maxCapacity', IntegerType::class, array(
                    'required' => true,
                    'label' => "Capacité globale de votre salle dans cette configuration",
                    'constraints' => [
                        new Assert\GreaterThanOrEqual(['value' => 0]),
                    ]
                ))
                ->add('onlyStandup', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Vous n\'avez que des places debout',
                ))
                ->add('nbStandUp', IntegerType::class, array(
                    'required' => false,
                    'label' => "Nombre de places debout",
                ))
                ->add('nbSeatedSeats', IntegerType::class, array(
                    'required' => false,
                    'label' => "Nombre de places assises (gradins)",
                ))
                ->add('nbBalconySeats', IntegerType::class, array(
                    'required' => false,
                    'label' => "Nombre de places assises (balcons)",
                ))
                ->add('pmrAccessible', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Votre salle est-elle accessible pour les personnes à mobilité réduite (PMR) ?',
                ))
                ->add('emailAddressPMR', EmailType::class, array(
                    'required' => false,
                    'label' => 'Adresse e-mail',
                ))
                ->add('phoneNumberPMR', TelType::class, array(
                    'required' => false,
                    'label' => 'Numéro de téléphone',
                ))
                ->add('hasFreeSeatingPolicy', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Toutes les places de cette configuration sont en placement libre (non-numérotées)',
                ))
                ->add('photo', PhotoType::class, array(
                    'label' => 'Plan de salle',
                    'required' => false,
                ))
            ;
        }
    }

    public function validate(VenueConfig $venueConfig, ExecutionContextInterface $context){
        if ($venueConfig->getMaxCapacity() === 0){
            $context->addViolation('Une salle doit avoir une capacité de plus de 0 places');
        }
        if ($venueConfig->isOnlyStandup()){
            $venueConfig->setNbBalconySeats(0);
            $venueConfig->setNbSeatedSeats(0);
            $venueConfig->setNbStandUp($venueConfig->getMaxCapacity());
        } else {
            if ($venueConfig->getNbStandUp() === null){
                $venueConfig->setNbStandUp(0);
            }
            if ($venueConfig->getNbSeatedSeats() === null){
                $venueConfig->setNbSeatedSeats(0);
            }
            if ($venueConfig->getNbBalconySeats() === null){
                $venueConfig->setNbBalconySeats(0);
            }
            if ($venueConfig->getNbStandUp() === 0 && $venueConfig->getNbSeatedSeats() === 0 && $venueConfig->getNbBalconySeats() === 0) {
                $context->addViolation('Si vous n\'avez pas que des places debout, vous devez renseigner le type (et le nombre) de places que vous possédez.');
            }
            $calculatedCapacity = $venueConfig->getNbStandUp() + $venueConfig->getNbSeatedSeats() + $venueConfig->getNbBalconySeats();
            if ($calculatedCapacity !== $venueConfig->getMaxCapacity()){
                $context->addViolation('La capacité maximale ne correspond pas à la somme des nombres de places...');
            }
        }
        if ($venueConfig->isPmrAccessible()){
            if ($venueConfig->getPhoneNumberPMR() === null && $venueConfig->getEmailAddressPMR() === null){
                $context->addViolation('Si vous avez un accès PMR, vous devez renseigner au minimum un numéro de téléphone ou une adresse mail de contact');
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => VenueConfig::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'block' => false,
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_venue_config_type';
    }

}