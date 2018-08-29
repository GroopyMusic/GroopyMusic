<?php

namespace AppBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\CounterPart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CounterPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'locales' => ['fr'],
                'fields' => [
                    'name' => [
                        'field_type' => TextType::class,
                        'label' => 'Nom',
                    ],
                    'description' => [
                        'field_type' => TextareaType::class,
                        'label' => 'Description',
                    ],
                ],
            ])
            ->add('isChildEntry', CheckboxType::class, array(
                'required' => false,
                'label' => "Il s'agit d'un ticket enfant",
            ))
            ->add('maximumAmount', IntegerType::class, array(
                'required' => true,
                'label' => 'Nombre en stock au total',
            ))
            ->add('price', NumberType::class, array(
                'required' => true,
                'label' => 'Prix',
            ))
            ->add('thresholdIncrease', NumberType::class, array(
                'required' => true,
                'label' => "Participation à l'augmentation du seuil",
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CounterPart::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_counter_part_type';
    }
}
