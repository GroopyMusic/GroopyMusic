<?php

namespace AppBundle\Form;

use AppBundle\Entity\CounterPart;
use AppBundle\Entity\PhysicalPerson;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhysicalPersonTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, array(
                'required' => true,
                'label' => 'Prénom',
            ))
            ->add('lastname', TextType::class, array(
                'required' => true,
                'label' => 'Nom',
            ))
            ->add('other_names', TextType::class, array(
                'required' => false,
                'label' => 'Autres noms à afficher entre parenthèses (société ou autre)'
            ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => 'Adresse e-mail',
            ))
            ->add('counterpart', EntityType::class, array(
                'class' => CounterPart::class,
                'multiple' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'Envoyer un ticket VIP',
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // allows ajax
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_physical_person_type';
    }
}