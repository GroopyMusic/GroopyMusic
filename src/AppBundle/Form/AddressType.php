<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, array(
                'label' => 'labels.address.street',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'address.street.long'])
                ],
            ))
            ->add('number', TextType::class, array(
                'label' => 'labels.address.number',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 10, 'maxMessage' => 'address.number.long'])
                ],
            ))
            ->add('zipcode', TextType::class, array(
                'label' => 'labels.address.zipcode',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 10, 'maxMessage' => 'address.zipcode.long'])
                ],
            ))
            ->add('city', TextType::class, array(
                'label' => 'labels.address.city',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 50, 'maxMessage' => 'address.city.long'])
                ],
            ))
            ->add('country', CountryType::class, array(
                'label' => 'labels.address.country',
                'placeholder' => 'Pays',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Country()
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
            'data_class' => 'AppBundle\Entity\Address'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_address';
    }


}
