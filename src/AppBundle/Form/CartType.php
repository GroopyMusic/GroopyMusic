<?php

namespace AppBundle\Form;

use AppBundle\Entity\Cart;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contracts', CollectionType::class, [
                'allow_add' => false,
                'entry_type' => ContractFanType::class,
                'entry_options' => [
                    'has_parent' => true,
                ]
            ])
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.contractfan.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cart::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_cart_type';
    }
}
