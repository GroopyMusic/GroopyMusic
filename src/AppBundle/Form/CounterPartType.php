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
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CounterPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'label' => false,
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
                'required' => false,
                'label' => 'Prix (en euros) (1 € ou plus)',
            ))
            ->add('thresholdIncrease', NumberType::class, array(
                'required' => true,
                'label' => "Poids (dans le sold out, mais aussi dans le seuil du financement participatif)",
            ))
            ->add('freePrice', CheckboxType::class, array(
                'attr' => ['class' => 'free-price-checkbox'],
                'required' => false,
                'label' => "Le prix doit être librement choisi par les acheteurs"
            ))
            ->add('minimumPrice', NumberType::class, array(
                'required' => false,
                'label' => "Prix minimum (en euros) (1 € ou plus)",
            ))
        ;
    }

    public function validate(CounterPart $counterPart, ExecutionContextInterface $context)
    {
        if($counterPart->getFreePrice()) {
            if($counterPart->getMinimumPrice() == null || $counterPart->getMinimumPrice() < 1) {
                $context->addViolation('Les prix des tickets doivent être au minimum de 1 €.');
            }
            $counterPart->setPrice($counterPart->getMinimumPrice());
        }
        else {
            if($counterPart->getPrice() == null || $counterPart->getPrice() < 1) {
                $context->addViolation('Les prix des tickets doivent être au minimum de 1 €.');
            }
            $counterPart->setMinimumPrice($counterPart->getPrice());
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CounterPart::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_counter_part_type';
    }
}
