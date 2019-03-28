<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\VenueConfig;
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
                'data' => 0,
            ))
            ->add('nbSeatedSeats', IntegerType::class, array(
                'required' => false,
                'label' => "Nombre de places assises (gradins)",
                'data' => 0,
            ))
            ->add('nbBalconySeats', IntegerType::class, array(
                'required' => false,
                'label' => "Nombre de places assises (balcons)",
                'data' => 0,
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
            ));
        if ($options['block']){
            $builder
                ->add('blocks', CollectionType::class, array(
                'label' => 'Les blocs des différentes configurations',
                'entry_type' => BlockType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'second-collection']
            ));
        }
    }

    public function validate(VenueConfig $venueConfig, ExecutionContextInterface $context){
        if ($venueConfig->getMaxCapacity() === 0){
            $context->addViolation('Une salle doit avoir une capacité de plus de 0 places');
        }
        if (!$venueConfig->isOnlyStandup()){
            if ($venueConfig->getNbStandUp() === 0 && $venueConfig->getNbSeatedSeats() === 0 && $venueConfig->getNbBalconySeats() === 0) {
                $context->addViolation('Si vous n\'avez pas que des places debout, vous devez renseigner le type (et le nombre) de places que vous possédez.');
            }
            $calculatedCapacity = $venueConfig->getNbStandUp() + $venueConfig->getNbSeatedSeats() + $venueConfig->getNbBalconySeats();
            if ($calculatedCapacity !== $venueConfig->getMaxCapacity()){
                $context->addViolation('La capacité maximale ne correspond pas à la somme des nombres de places...');
            }
        } else {
            $venueConfig->setNbStandUp($venueConfig->getMaxCapacity());
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
            'block' => false,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_venue_config_type';
    }

}