<?php

namespace AppBundle\Form;

use AppBundle\Entity\PropositionContractArtist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
                'label' => 'labels.proposition_contract_artist.province',
                'multiple' => false,
                'remote_route' => 'select2_provinces',
                'class' => 'AppBundle\Entity\Province',
                'primary_key' => 'id',
            ])
            ->add('artist', Select2EntityType::class, [
                'required' => false,
                'label' => 'labels.proposition_contract_artist.artists',
                'multiple' => false,
                'remote_route' => 'select2_artists',
                'class' => 'AppBundle\Entity\Artist',
                'primary_key' => 'id',
            ])
            ->add('reason', TextareaType::class, array(
                'label' => 'labels.proposition_contract_artist.reason',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10, 'minMessage' => 'La raison doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('nb_expected', IntegerType::class, array(
                'label' => 'labels.proposition_contract_artist.nb_expected',
                'required' => true,
                'attr' => array('min' => 0),
                'constraints' => [
                    new NotBlank(),
                    new Range(array(
                        'min'        => 0,
                        'minMessage' => 'Nombre peut pas etre negatif',
                    )),
                ],
            ))
            ->add('payable', CheckboxType::class, array(
                'label' => 'labels.proposition_contract_artist.payable',
                'required' => false
            ))
            ->add('period_start_date', DateType::class, array(
                'label' => 'labels.proposition_contract_artist.period_start_date',
                'required' => true,
                'placeholder' => array(
                    'year' => 'placeholders.proposition_contract_artist.year',
                    'month' => 'placeholders.proposition_contract_artist.month',
                    'day' => 'placeholders.proposition_contract_artist.day',
                ),
                'constraints' => [
                    new NotBlank(),
                ],
            ))
            ->add('period_end_date', DateType::class, array(
                'label' => 'labels.proposition_contract_artist.period_end_date',
                'placeholder' => array(
                    'year' => 'placeholders.proposition_contract_artist.year',
                    'month' => 'placeholders.proposition_contract_artist.month',
                    'day' => 'placeholders.proposition_contract_artist.day',
                ),
                'required' => false,
            ))
            ->add('day_commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contract_artist.day_commentary',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 10, 'minMessage' => 'L\'avis sur le jour doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contract_artist.commentary',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 10, 'minMessage' => 'Le commentaire doit faire au minimum {{ limit }} caractères.'])
                ],
            ))
            ->add('submit', ButtonType::class, array(
                'label' => 'labels.proposition_contract_artist.submit',
                'attr' => ['class' => 'btn btn-primary myPropositionForm'],
            ))
        ;
    }
    public function validate(PropositionContractArtist $propositionContractArtist, ExecutionContextInterface $context) {
        if($propositionContractArtist->getPropositionHall() == NULL && $propositionContractArtist->getProvince() == NULL) {
            $context->addViolation( "Lors de la soumission du formulaire, il faut au minimum soit une province, soit les renseignements de la salle");
        }
        if($propositionContractArtist->getArtist()==NULL && $propositionContractArtist->getPropositionArtist()==NULL) {
            $context->addViolation( "Lors de la soumission du formulaire, il faut au minimum soit une proposition d'artiste, soit un artiste déjà existant");
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionContractArtist',
            'csrf_protection' => false,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_contract_artist';
    }

}