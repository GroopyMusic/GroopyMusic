<?php

namespace AppBundle\Form;

use AppBundle\Entity\SuggestionBox;
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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SuggestionBoxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, array(
                'label' => 'labels.suggestionbox.type',
                'class' => SuggestionTypeEnum::class,
            ))
            ->add('name', TextType::class, array(
                'label' => 'labels.suggestionbox.name',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 64, 'maxMessage' => 'Le nom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            ->add('firstname', TextType::class, array(
                'label' => 'labels.suggestionbox.firstname',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 64, 'maxMessage' => 'Le prénom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            ->add('email', TextType::class, array(
                'label' => 'labels.suggestionbox.email',
                'required' => false,
                'constraints' => [
                    new Email(['message' => 'Cette adresse e-mail est invalide.']),
                ]
            ))
            ->add('object', TextType::class, array(
                'label' => 'labels.suggestionbox.object',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un objet.']),
                    new Length(['max' => 64, 'maxMessage' => "L'objet ne peut dépasser {{ limit }} caractères"]),
                ]
            ))
            ->add('message', TextareaType::class, array(
                'label' => 'labels.suggestionbox.message',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un message.']),
                    new Length(['min' => 20, 'minMessage' => "Le message doit contenir au moins {{ limit }} caractères."]),
                ]
            ))
            ->add('mailCopy', CheckboxType::class, array(
                'label' => 'labels.suggestionbox.mailcopy',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'labels.suggestionbox.submit',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SuggestionBox::class,
        ));
    }

     /**
      * {@inheritdoc}
      */
     public function getBlockPrefix()
     {
         return 'app_suggestionbox';
     }


}
