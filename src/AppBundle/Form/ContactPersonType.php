<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/02/2018
 * Time: 17:35
 */

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                    new Length(['max' => 255, 'maxMessage' => 'contact_person.firstname.max'])
                ],
            ))
            ->add('lastname', TextType::class, array(
                'label' => 'labels.contact_person.lastname',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'contact_person.lastname.max'])
                ],
            ))
            ->add('phone', TextType::class, array(
                'label' => 'labels.contact_person.phone',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 20, 'maxMessage' => 'contact_person.phone.max'])
                ],
            ))
            ->add('mail', EmailType::class, array(
                'label' => 'labels.contact_person.mail',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'contact_person.mail.max'])
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