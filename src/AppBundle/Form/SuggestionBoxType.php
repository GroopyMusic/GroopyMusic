<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SuggestionBoxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('firstname', TextType::class)
            ->add('email', TextType::class)
            ->add('object', TextType::class)
            ->add('message', TextAreaType::class)
            ->add('mailCopy', CheckboxType::class, array('required' => false))
            ->add('submit', SubmitType::class);
    }
    
    
    
    // /**
    //  * {@inheritdoc}
    //  */
    // public function configureOptions(OptionsResolver $resolver)
    // {
    //     $resolver->setDefaults(array(
    //         'data_class' => 'AppBundle\Entity\SuggestionBox'
    //     ));
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getBlockPrefix()
    // {
    //     return 'appbundle_suggestionbox';
    // }


}
