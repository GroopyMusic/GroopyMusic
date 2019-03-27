<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bankAccount', TextType::class, array(
                'required' => true,
                'label' => 'Numéro de compte en banque IBAN',
            ))
            ->add('vatNumber', TextType::class, array(
                'required' => true,
                'label' => 'Numéro de TVA',
            ))
            ->add('organizationName', TextType::class, array(
                'required' => true,
                'label' => "Nom de l'organisation",
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Mettre à jour',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_user_bank_account_type';
    }
}
