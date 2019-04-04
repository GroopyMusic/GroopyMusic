<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\YB\VenueConfig;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\YBSubEvent;
use AppBundle\Form\AddressType;
use AppBundle\Form\CounterPartType;
use AppBundle\Form\PhotoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Common\Collections\ArrayCollection;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

class YBContractArtistInfosType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['admin']){
            $builder
                ->add('commissions', CollectionType::class, array(
                    'label' => 'Commissions',
                    'entry_type' => YBCommissionType::class,
                    'entry_options' => array(
                        'label' => false,
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'attr' => ['class' => 'second-collection']
                    //'required' => false,
                    //'label' => 'Montant fixe minimum',
                ))
                ->add('vat', ChoiceType::class, array(
                    'required' => false,
                    'label' => 'Taux de TVA',
                    'choices' => array(
                        "0%" => 0,
                        "6%" => 0.06,
                        "12%" => 0.12,
                        "21%" => 0.21),
                    'constraints' => [
                        new Assert\GreaterThanOrEqual(['value' => 0]),
                        new Assert\LessThanOrEqual(['value' => 1])
                    ]
                ))
            ;
        }
        $builder
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'label' => 'Organisation',
                'choices' => $options['userOrganizations'],
                'group_by' => function(Organization $org){
                    if ($org->isPrivate()){
                        return 'Personnellement';
                    } else {
                        return 'Mes organisations';
                    }
                },
                'choice_label' => 'name',
            ])
            ->add('venue', EntityType::class, [
                'class' => VenueConfig::class,
                'label' => 'Salle',
                'choices' => $options['venues'],
                'choice_label' => 'displayName',
            ])
            ->add('threshold', IntegerType::class, array(
                'required' => false,
                'label' => 'Seuil de validation',
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                ]
            ))
            ->add('dateEnd', DateTimeType::class, array(
                'required' => false,
                'label' => 'Date de validation',
            ))
            ->add('dateClosure', DateTimeType::class, array(
                'required' => true,
                'label' => 'Fin des ventes',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('dateEvent', DateTimeType::class, array(
                'required' => false,
                'label' => "Date de l'événement"
            ))
            ->add('noSubEvents', CheckboxType::class, array(
                'label' => "A une seule date",
                'required' => false,
            ))
            ->add('subEvents', CollectionType::class, array(
                'label' => 'Dates',
                'entry_type' => YBSubEventType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'collection']
                //'required' => false,
                //'label' => 'Montant fixe minimum',
            ))
            ->add('address', AddressType::class, array(
                'required' => true,
                'label' => "Lieu de l'événement",
                'constraints' => [
                    new Assert\Valid(),
                ]
            ))
            ->add('translations', TranslationsType::class, [
                'locales' => ['fr'],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => 'Titre',
                        'constraints' => [
                            new Assert\NotBlank(),
                        ]
                    ],
                    'description' => [
                        'field_type' => 'ckeditor',
                        'label' => 'Description',
                        'constraints' => [
                            new Assert\NotBlank(),
                        ],
                        'config_name' => 'bbcode',
                    ],
                ],
                'exclude_fields' => ['additional_info', 'slug']
            ])
            ->add('photo', PhotoType::class, array(
                'label' => 'Photo de couverture',
                'required' => false,
            ))
        ;

        if($options['creation']) {
            $builder
                ->add('noThreshold', CheckboxType::class, array(
                    'label' => "N'a pas de seuil de validation",
                    'required' => false,
                ))
                ->add('acceptConditions', CheckboxType::class, array(
                    'label' => "J'ai lu et j'accepte les conditions d'utilisation de la plateforme Ticked-it!",
                    'required' => true,
                    'constraints' => array(
                        new Assert\NotBlank(),
                    )
                ));
        } else {
            $builder
                ->add('bankAccount', TextType::class, array(
                    'label' => 'Numéro de compte en banque IBAN',
                    'required' => false
                ))
                ->add('vatNumber', TextType::class, array(
                    'label' => 'Numéro de TVA',
                    'required' => false
                ))
            ;
        }

    }

    public function validate(YBContractArtist $campaign, ExecutionContextInterface $context)
    {
        if(!$campaign->getNoThreshold()) {
            if($campaign->getThreshold() <= 0) {
                $context->addViolation('Puisque la campagne a un seuil de validation, il faut préciser ce seuil, qui doit être supérieur à 0.');
            }
            $today_90 = clone $campaign->getDate();
            $today_90->modify('+ 90 days');
            if($campaign->getDateEnd() == null || $campaign->getDateEnd() < ($campaign->getDate()) || $campaign->getDateEnd() > $campaign->getDateClosure() || $campaign->getDateEnd() > $today_90) {
                $context->addViolation('Puisque la campagne a un seuil de validation, il faut préciser une date de validation valide, antérieure à la date de fin des ventes et maximum 90 jours après la date de création de la campagne.');
            }
        }
        else {
            $campaign->setTicketsSent(true);
        }

        if(!$campaign->hasSubEvents() && $campaign->getDateEvent() == null) {
            $context->addViolation('La campagne doit soit avoir une date fixe, soit au moins un sous-événement');
        }

        if($campaign->hasSubEvents() && count($campaign->getSubEvents()) == 0) {
            $context->addViolation('La campagne doit soit avoir une date fixe, soit au moins un sous-événement');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YBContractArtist::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'creation' => false,
            'admin' => false,
            'userOrganizations' => null,
            'venues' => null,
            'campaign_id' => null,
            'has_sub_events' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_type';
    }
}
