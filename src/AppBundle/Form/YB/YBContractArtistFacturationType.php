<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
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

class YBContractArtistFacturationType extends AbstractType
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
            'campaign_id' => null,
            'has_sub_events' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_facturation_type';
    }
}
