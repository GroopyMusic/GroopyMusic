<?php

namespace AppBundle\Form;

use AppBundle\Entity\Purchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contract_artist = $options['contract_artist'];

        $builder
            ->add('quantity', ChoiceType::class, array(
                'choices' => range(0, min(20, $contract_artist->getStep()->getMaxTickets() - $contract_artist->getTicketsSold())),
                'label' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Purchase::class,
            'contract_artist' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_purchase_type';
    }
}
