<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Form\CounterPartType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class YBContractArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('noThreshold', CheckboxType::class, array(
                'label' => "N'a pas de seuil de validation",
                'required' => false,
            ))
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
                'label' => "Date de l'événement (si applicable)"
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
                        'field_type' => TextareaType::class,
                        'label' => 'Description',
                        'constraints' => [
                            new Assert\NotBlank(),
                        ]
                    ],
                ],
                'exclude_fields' => ['additional_info', 'slug']
            ])
            ->add('counterParts',  CollectionType::class, array(
                'label' => 'Articles/Tickets en vente',
                'entry_type' => CounterPartType::class,
                'entry_options' => array(
                    'label' => false,
                ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
            ))
            ->add('globalSoldout', NumberType::class, array(
                'label' => 'Sold out global (si applicable)',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer',
            ))
        ;

        if($options['creation']) {
            $builder->add('acceptConditions', CheckboxType::class, array(
                'label' => "J'ai lu et j'accepte les conditions d'utilisation de la plateforme Ticked-it!",
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                )
            ));
        }
    }

    public function validate(YBContractArtist $campaign, ExecutionContextInterface $context)
    {
        if(count($campaign->getCounterParts()) == 0) {
            $context->addViolation('Il faut au moins un article en vente pour que la campagne soit valide.');
        }
        if(!$campaign->getNoThreshold()) {
            if($campaign->getThreshold() <= 0) {
                $context->addViolation('Puisque la campagne a un seuil de validation, il faut préciser ce seuil, qui doit être supérieur à 0.');
            }
            if($campaign->getDateEnd() == null || $campaign->getDateEnd() < ($campaign->getDate()) || $campaign->getDateEnd() > $campaign->getDateClosure()) {
                $context->addViolation('Puisque la campagne a un seuil de validation, il faut préciser une date de validation valide, antérieure à la date de fin des ventes.');
            }
        }

        if($campaign->getDateEvent() != null) {
            if ($campaign->getDateEvent() < $campaign->getDate()) {
                $context->addViolation("La date de l'événement doit être dans le futur.");
            }
            if (($campaign->getDateEnd() != null && $campaign->getDateEnd() > $campaign->getDateEvent()) || ($campaign->getDateClosure() > $campaign->getDateEvent())) {
                $context->addViolation("Puisque la campagne a une date d'événement, il faut que la date de fin de ventes et l'éventuelle date de validation du financement lui soient antérieures.");
            }
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
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_type';
    }
}
