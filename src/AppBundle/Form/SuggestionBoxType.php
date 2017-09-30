<?php

namespace AppBundle\Form;

use AppBundle\Entity\SuggestionTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('type', EntityType::class, array(
                'class' => SuggestionTypeEnum::class,
            ))
            ->add('name', TextType::class, array(
                'required' => false,
                'attr' => ['class' => 'suggestion_form_name']
            ))
            ->add('firstname', TextType::class, array(
                'required' => false,
                'attr' => ['class' => 'suggestion_form_firstname']
            ))
            ->add('email', TextType::class, array(
                'required' => false,
                'attr' => ['class' => 'suggestion_form_email']
            ))
            ->add('object', TextType::class)
            ->add('message', TextareaType::class)
            ->add('mailCopy', CheckboxType::class, array(
                'required' => false
            ))
            ->add('submit', SubmitType::class);
    }


     /**
      * {@inheritdoc}
      */
     public function getBlockPrefix()
     {
         return 'app_suggestionbox';
     }


}
