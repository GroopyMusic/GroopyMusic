<?php

namespace XBundle\Form;

use AppBundle\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class XAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, array(
                'label' => 'labels.address.street',
                'required' => false
            ))
            ->add('number', TextType::class, array(
                'label' => 'labels.address.number',
                'required' => false
            ))
            ->add('zipcode', TextType::class, array(
                'label' => 'labels.address.zipcode',
                'required' => false
            ))
            ->add('city', TextType::class, array(
                'label' => 'labels.address.city',
                'required' => false
            ))
            ->add('country', CountryType::class, array(
                'label' => 'labels.address.country',
                'placeholder' => 'Pays',
                'required' => false
            ))
            ->add('name', TextType::class, array(
                'label' => 'labels.address.name',
                'required' => false
            ))
        ;
    }

    public function validate(Address $address = null, ExecutionContextInterface $context)
    {
        if($address != null) {
            if($address->getStreet() == null || $address->getNumber() == null ||
                $address->getZipcode() == null || $address->getCity() == null || $address->getCountry() == null) {
                $context->addViolation('Tous les champs de l\'adresse doivent être complétés');
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Address',
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_address_type';
    }


}
