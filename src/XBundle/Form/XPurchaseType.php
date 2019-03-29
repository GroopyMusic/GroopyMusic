<?php

namespace XBundle\Form;

use XBundle\Entity\Project;
use XBundle\Entity\XPurchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XPurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Project $project */
        $project = $options['project'];

        $builder->add('quantity', NumberType::class, array(
            'attr' => [
                'class' => 'quantity',
            ],
            'label' => false,
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($project) {
            $purchase = $event->getData();
            if($purchase->getProduct()->getFreePrice()) {
                $event->getForm()->add('freePrice', NumberType::class, array(
                    'attr' => [
                        'class' => 'free-price-value product-price',
                    ],
                    'label' => false,
                ));
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => XPurchase::class,
            'project' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'xbundle_xpurchase_type';
    }
}
