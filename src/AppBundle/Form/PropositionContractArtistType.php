<?php

namespace AppBundle\Form;

use Sonata\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class PropositionContractArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contactPerson',     ContactPersonType::class)
            ->add('propositionHall',     PropositionHallType::class)
            ->add('propositionArtist',     PropositionArtistType::class)
            ->add('province', Select2EntityType::class, [
                'required' => false,
                'label' => 'labels.proposition_contractArtist.province',
                'multiple' => false,
                'remote_route' => 'select2_provinces',
                'class' => 'AppBundle\Entity\Province',
                'primary_key' => 'id',
            ])
            ->add('artists', Select2EntityType::class, [
                'required' => true,
                'label' => 'labels.proposition_contractArtist.artists',
                'multiple' => false,
                'remote_route' => 'select2_artists',
                'class' => 'AppBundle\Entity\Artist',
                'primary_key' => 'id',
            ])
            ->add('reason', TextareaType::class, array(
                'label' => 'labels.proposition_contractArtist.reason',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'minMessage' => 'La raison doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('nb_expected', IntegerType::class, array(
                'label' => 'labels.proposition_contractArtist.nb_expected',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ))
            ->add('payable', BooleanType::class, array(
                'label' => 'labels.proposition_contractArtist.payable',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ))
            ->add('period_start_date', DateType::class, array(
                'label' => 'labels.proposition_contractArtist.period_start_date',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ))
            ->add('period_end_date', DateType::class, array(
                'label' => 'labels.proposition_contractArtist.period_end_date',
                'required' => false,
            ))
            ->add('day_commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contractArtist.day_commentary',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'minMessage' => 'L\'avis sur le jour doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contractArtist.commentary',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'minMessage' => 'Le commentaire doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.proposition_contractArtist.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionContractArtist'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_contract_artist';
    }

}