<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class UserEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('asked_email', RepeatedType::class, array(
                'first_options' => ['label' => 'labels.user.askedEmail.first'],
                'second_options' => ['label' => 'labels.user.askedEmail.second'],
                'required' => true,
                'constraints' => [
                    new Email(['message' => 'fos_user.email.invalid']),
                ]
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.user.newemail.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_user_email_change';
    }


}
