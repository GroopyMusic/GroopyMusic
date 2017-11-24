<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist_User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Artist_UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', TextType::class, array(
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner ce champ.']),
                    new Length(['max' => 63, 'maxMessage' => 'Le rôle ne peut dépasser {{ limit }} caractères.']),
                ]
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.artist_user.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Artist_User::class,
        ));
    }

    public function getName()
    {
        return 'app_bundle_artist_user_type';
    }
}
