<?php

namespace AppBundle\Form;

use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('submit', SubmitType::class, array(
            'label' => 'change_password.submit',
            'attr' => [
                'class' => 'btn btn-primary',
            ]
        ));
    }


    public function getParent()
    {
        return ChangePasswordFormType::class;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'FOSUserBundle',
        ]);
    }

    public function getName()
    {
        return 'app_bundle_change_password_type';
    }
}
