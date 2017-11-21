<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('username')
            ->remove('email')
            // Duplicated from RegistrationType
            ->add('lastname', TextType::class, array(
                'label' => 'labels.user.profile.lastname',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un nom de famille.']),
                    new Length(['max' => 64, 'maxMessage' => 'Le nom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            ->add('firstname', TextType::class, array(
                'label' => 'labels.user.profile.firstname',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Merci de renseigner un prénom.']),
                    new Length(['max' => 64, 'maxMessage' => 'Le prénom ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            // End duplicated
            ->remove('current_password')
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.user.profile.submit'
            ))
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';

    }

    public function getBlockPrefix()
    {
        return 'app_user_profile';
    }
}