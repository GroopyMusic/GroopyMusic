<?php

namespace XBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use XBundle\Entity\Product;
use XBundle\Form\ImageType;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Intitulé',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('description', 'ckeditor', array(
                'label' => 'Description',
                'config_name' => 'bbcode',
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ))
            ->add('supply', IntegerType::class, array(
                'label' => 'Nombre en stock au total',
            ))
            ->add('maxAmountPerPurchase', IntegerType::class, array(
                'label' => 'Nombre maximum par achat',
            ))
            ->add('price', NumberType::class, array(
                'label' => 'Prix (en euros)',
                'required' => false
            ))
            ->add('freePrice', CheckboxType::class, array(
                'label' => "Le prix doit être librement choisi par les contributeurs",
                'attr' => ['class' => 'free-price-checkbox'],
                'required' => false,
            ))
            ->add('minimumPrice', NumberType::class, array(
                'label' => "Prix minimum (en euros) (1 € ou plus)",
                'required' => false,
            ))
            ->add('photo', ImageType::class, array(
                'label' => 'Photo',
                'required' => false
            ))
            ->add('isTicket', CheckboxType::class, array(
                'label' => 'L\'article mis en vente est un ticket',
                'attr' => ['class' => 'is-ticket-checkbox'],
                'required' => false
            ))
        ;

        if ($options['creation'] || $options['is_edit']) {
            $builder
                ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer'
                ))
            ;
        }
    }
    

    public function validate(Product $product, ExecutionContextInterface $context)
    {
        if($product->getSupply() < 1) {
            $context->addViolation('Le nombre en stock au total doit être minimum de 1');
        }
        
        if($product->getMaxAmountPerPurchase() < 1) {
            $context->addViolation('La quantité max de chaque article par achat doit être minimum de 1');
        }

        if($product->getFreePrice()) {
            if($product->getMinimumPrice() == null || $product->getMinimumPrice() < 1) {
                $context->addViolation('Si le prix est libre, il doit être de minimum 1 €');         }
            $product->setPrice($product->getMinimumPrice());
        } else {
            if ($product->getPrice() == null || $product->getPrice() < 1) {
                $context->addViolation('Le prix doit être de minimum 1 €');
            }
            $product->setMinimumPrice($product->getPrice());
        }

    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'creation' => false,
            'is_edit' =>false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_product_type';
    }


}
