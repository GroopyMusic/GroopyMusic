<?php

namespace XBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Entity\ChoiceOption;
use XBundle\Entity\OptionProduct;

class XPurchaseChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var OptionProduct $option */
        $option = $options['option'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($option) {
            $event->getForm()->add('choices', EntityType::class, array(
                'class' => ChoiceOption::class,
                'placeholder' => '',
                'empty_data' => null,
                'label' => $option->getName(),
                'query_builder' => function (EntityRepository $er) use ($option) {
                    return $er->createQueryBuilder('co')
                        ->where('co.option = :option')
                        ->setParameter('option', $option)
                    ;
                },
            ));
        });

        /*$builder
            ->add('choices', EntityType::class, array(
                'class' => ChoiceOption::class,
                'placeholder' => '',
                'empty_data' => null,
                'label' => false
            ))
        ;*/

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OptionProduct::class,
            'option' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'xbundle_xpurchase_choice_type';
    }
}
