<?php

namespace XBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Entity\Project;
use XBundle\Entity\XPurchase;
use XBundle\Entity\ChoiceOption;
use XBundle\Form\XPurchaseChoiceType;

class XPurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Project $project */
        $project = $options['project'];

        $builder
            ->add('quantity', NumberType::class, array(
                'attr' => [
                    'class' => 'quantity',
                ],
                'label' => false,
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($project) {
            $purchase = $event->getData();
            if($purchase->getProduct()->getFreePrice()) {
                $event->getForm()->add('freePrice', NumberType::class, array(
                    'attr' => [
                        'class' => 'free-price-value product-price',
                    ],
                    'label' => false,
                    'required' => false
                ));
            }
            /*if(!empty($purchase->getProduct()->getOptions())) {
                foreach ($purchase->getProduct()->getOptions() as $option) {
                    $event->getForm()->add('options', CollectionType::class, array(
                        'label' => false,
                        'allow_add' => false,
                        'entry_type' => XPurchaseChoiceType::class,
                        'entry_options' => [
                            'option' => $option,
                        ],
                    ));
                }
            }*/

            /*$event->getForm()->add('choices', EntityType::class, array(
                    'class' => ChoiceOption::class,
                    'placeholder' => '',
                    'empty_data' => null,
                ));*/
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
