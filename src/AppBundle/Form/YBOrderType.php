<?php

namespace AppBundle\Form;

use AppBundle\Entity\YB\YBOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YBOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('last_name', TextType::class, array(
                'required' => true,
                'label' => 'Nom/Last name',
            ))
            ->add('first_name', TextType::class, array(
                'required' => true,
                'label' => 'Prénom/First name',
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => 'Adresse e-mail/Email address',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults([
            'data_class' => YBOrder::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_yborder_type';
    }
}
