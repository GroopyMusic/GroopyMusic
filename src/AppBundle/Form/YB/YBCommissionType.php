<?php

namespace AppBundle\Form\YB;


use AppBundle\Entity\YB\YBCommission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class YBCommissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('minimumThreshold', NumberType::class, array(
                'required' => false,
                'label' => 'Coût acheteur minimum',
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0])
                ]
            ))
            ->add('fixedAmount', NumberType::class, array(
                'required' => false,
                'label' => 'Commission fixe',
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0])
                ]
            ))
            ->add('percentageAmount', PercentType::class, array(
                'required' => false,
                'label' => 'Pourcentage sur coût organisateur',
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                    new Assert\LessThan(['value' => 100])
                ]
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YBCommission::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ]);
    }

    public function validate(YBCommission $commission, ExecutionContextInterface $context)
    {
        return true;
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_commission_type';
    }
}