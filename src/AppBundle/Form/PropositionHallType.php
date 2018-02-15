<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 14/02/2018
 * Time: 17:04
 */

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
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
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'Le nom de le la salle ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('contact_email', TextType::class, array(
                'label' => 'labels.proposition_hall.contact_email',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'L\'email ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('contact_phone', TextType::class, array(
                'label' => 'labels.proposition_hall.contact_phone',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => 'Le numero de télephone ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('province', Select2EntityType::class, [
                'required' => false,
                'label' => 'labels.proposition_hall.province',
                'multiple' => false,
                'remote_route' => 'select2_provinces',
                'class' => 'AppBundle\Entity\province',
                'primary_key' => 'id',
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