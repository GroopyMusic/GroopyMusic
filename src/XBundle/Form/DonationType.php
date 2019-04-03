<?php

namespace XBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use XBundle\Entity\XContractFan;

class DonationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', NumberType::class, [
                'attr' => [
                    'class' => 'amount-donation',
                ],
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0])
                ]
            ])
            ->add('submit', SubmitType::class, array(
                'label' => 'Valider'
            ));
    }

    public function validate(XContractFan $contractFan, ExecutionContextInterface $context)
    {
        if ($contractFan->getAmount() <= 0) {
            $context->addViolation('Le montant du don doit être minimum de 1 €');
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => XContractFan::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_donation_type';
    }


}
