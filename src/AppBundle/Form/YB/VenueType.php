<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\VenueConfig;
use AppBundle\Form\PhotoType;
use Symfony\Component\Form\AbstractType;
use AppBundle\Form\AddressType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\YB\Organization;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VenueType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['block']) {
            $builder
                ->add('configurations', CollectionType::class, array(
                    'label' => 'Configurations différentes de la salle',
                    'entry_type' => VenueConfigType::class,
                    'entry_options' => array(
                        'label' => false,
                        'block' => true,
                    ),
                    'allow_add' => false,
                    'allow_delete' => false,
                    'by_reference' => false,
                    'prototype' => true,
                ))
                ->add('submit', SubmitType::class, array(
                    'label' => 'Enregistrer',
                ));
        } else {
            $builder
                ->add('organization', EntityType::class, [
                    'class' => Organization::class,
                    'label' => 'Organisation',
                    'choices' => $options['userOrganizations'],
                    'group_by' => function (Organization $org) {
                        if ($org->isPrivate()) {
                            return 'Personnellement';
                        } else {
                            return 'Mes organisations';
                        }
                    },
                    'choice_label' => 'name',
                ])
                ->add('address', AddressType::class, array(
                    'required' => false,
                    'label' => "Lieu de l'événement",
                ))
                ->add('submit', SubmitType::class, array(
                    'label' => 'Enregistrer',
                ))
                ->add('configurations', CollectionType::class, array(
                    'label' => 'Configurations différentes de la salle',
                    'entry_type' => VenueConfigType::class,
                    'entry_options' => array(
                        'label' => false,
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'attr' => ['class' => 'collection'],
                ));
            if ($options['creation']) {
                $builder
                    ->add('acceptConditions', CheckboxType::class, array(
                        'label' => "J'ai lu et j'accepte les conditions d'utilisation de la plateforme Ticked-it!",
                        'required' => true,
                        'constraints' => array(
                            new Assert\NotBlank(),
                        )))
                    ->add('accept_being_responsible', CheckboxType::class, array(
                        'label' => "Je confirme être le gestionnaire de la salle.",
                        'required' => false,
                    ))
                    ->add('accept_venue_temp', CheckboxType::class, array(
                        'label' => "Je ne suis pas le gestionnaire de la salle et j'accepte que la salle que je crée soit éphémère et qu'elle sera supprimée après mon événement.",
                        'required' => false,
                    ));
            }
        }
    }

    public function validate(Venue $venue, ExecutionContextInterface $context)
    {
        if (!$venue->getAcceptBeingResponsible() && !$venue->getAcceptVenueTemp()){
            $context->addViolation("Soit vous êtes gestionnaire de la salle et vous accepter qu'elle soit enregistrée dans le système, soit vous acceptez que la salle soit supprimée après votre événement.");
        }
        if ($venue->getAcceptBeingResponsible() && $venue->getAcceptVenueTemp()){
            $context->addViolation("Soit vous êtes gestionnaire de la salle et vous accepter qu'elle soit enregistrée dans le système, soit vous acceptez que la salle soit supprimée après votre événement.");
        }
        if (count($venue->getConfigurations()) === 0) {
            $context->addViolation("Vous devez enregistrer au moins 1 configuration !");
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Venue::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'userOrganizations' => null,
            'admin' => false,
            'creation' => false,
            'block' => false,
        ));
    }

}