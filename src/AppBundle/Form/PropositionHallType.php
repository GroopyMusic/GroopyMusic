<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/02/2018
 * Time: 17:04
 */

namespace AppBundle\Form;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class PropositionHallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'labels.proposition_hall.name',
                'required' => true,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'proposition_hall.name.max'])
                ],
            ))
            ->add('contact_email', EmailType::class, array(
                'label' => 'labels.proposition_hall.contact_email',
                'required' => true,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'proposition_hall.contact_email.max'])
                ],
            ))
            ->add('contact_phone', TextType::class, array(
                'label' => 'labels.proposition_hall.contact_phone',
                'required' => true,
                'constraints' => [
                    new Length(['max' => 20, 'maxMessage' => 'proposition_hall.contact_phone.max'])
                ],
            ))
            ->add('province', EntityType::class, [
                'required' => true,
                'label' => 'labels.proposition_contract_artist.province',
                'multiple' => false,
                'class' => 'AppBundle\Entity\Province'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionHall'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_hall';
    }
}