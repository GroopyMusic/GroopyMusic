<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/02/2018
 * Time: 17:35
 */

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactPersonType  extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, array(
                'label' => 'labels.contact_person.firstname',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'Le prénom ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'labels.contact_person.lastname',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'Le nom ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('phone', TextType::class, array(
                'label' => 'labels.contact_person.phone',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 20, 'maxMessage' => 'Le numero de télephone ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('mail', TextType::class, array(
                'label' => 'labels.contact_person.mail',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'L\'email ne peut excéder {{ limit }} caractères.'])
                ],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ContactPerson'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_contact_person';
    }
}