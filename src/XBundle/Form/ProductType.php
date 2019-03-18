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
                'required' => 'true'
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description',
                'required' => 'true'
            ))
            ->add('price', NumberType::class, array(
                'label' => 'Prix (en euros)',
                'required' => 'true'
            ))
            ->add('supply', IntegerType::class, array(
                'label' => 'Quantité à mettre en vente',
                'required' => 'true'
            ))
            ->add('photo', ImageType::class, array(
                'label' => 'Photo',
                'required' => 'false'
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer'
            ));;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class
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
