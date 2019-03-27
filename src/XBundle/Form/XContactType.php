<?php

namespace XBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use XBundle\Entity\XContact;

class XContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'label' => false,
                'attr' => ['placeholder' => 'Nom'],
                'constraints' => array(
                    new NotBlank(),
                    new Length([
                        'max' => 50,
                    ]),
                ),
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => false,
                'attr' => ['placeholder' => 'Email'],
                'constraints' => [
                    new Email(),
                    new NotBlank(),
                    new Length(['max' => 60]),
                ],
            ))
            ->add('message', TextareaType::class, array(
                'required' => true,
                'label' => false,
                'attr' => ['placeholder' => 'Message'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ]
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => XContact::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_xcontact';
    }


}
