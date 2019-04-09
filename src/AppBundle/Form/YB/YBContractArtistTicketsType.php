<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\Organization;
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

class YBContractArtistTicketsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $class_attr = $options['data']->hasSubEvents() ? '' : 'no-sub-events';
        $class2_attr = count($options['data']->getConfig()->getBlocks()) > 0 ? '' : 'no-blk';
        $builder
            ->add('counterParts',  CollectionType::class, array(
                'label' => 'Tickets en vente',
                'entry_type' => CounterPartType::class,
                'entry_options' => array(
                    'attr' => [
                        'class' => $class_attr,
                        'class2' => $class2_attr,
                    ],
                    'label' => false,
                    'campaign_id' => $options['data']->getId(),
                    'config' => $options['data']->getConfig(),
                    'has_sub_events' => $options['data']->hasSubEvents(),
                    'has_venue' => $options['data']->getVenue() !== null,
            ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => ['class' => 'collection'],
            ))
            ->add('globalSoldout', NumberType::class, array(
                'label' => 'Sold out global',
                'required' => false,
            ))
        ;

    }

    public function validate(YBContractArtist $campaign, ExecutionContextInterface $context){
        if(count($campaign->getCounterParts()) == 0) {
            $context->addViolation('Il faut au moins un article en vente pour que la campagne soit valide.');
        }
    }

    public function configureOptions(OptionsResolver $resolver){
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
            'em' => null,
            'user' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_type';
    }
}
