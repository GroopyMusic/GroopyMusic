<?php

namespace AppBundle\Form;

use AppBundle\Entity\PropositionArtist;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\PropositionHall;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('contactPerson', ContactPersonType::class)
            ->add('propositionHall', PropositionHallType::class)
            ->add('propositionArtist', PropositionArtistType::class)
            ->add('province', EntityType::class, [
                'required' => true,
                'label' => 'labels.proposition_contract_artist.province',
                'multiple' => false,
                'class' => 'AppBundle\Entity\Province'
            ])
            ->add('radioPropositionType', ChoiceType::class, [
                'label' => 'labels.proposition_contract_artist.ask_if_concert',
                'choices' => array(
                    'choices.proposition_contract_artist.yes' => true,
                    'choices.proposition_contract_artist.no' => false,
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'proposition-type']
            ])
            ->add('radioArtist', ChoiceType::class, [
                'label' => 'labels.proposition_contract_artist.ask_if_artist',
                'choices' => array(
                    'choices.proposition_contract_artist.yes' => true,
                    'choices.proposition_contract_artist.no' => false,
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'artist-choice'],
            ])
            ->add('radioHall', ChoiceType::class, [
                'label' => 'labels.proposition_contract_artist.ask_if_hall',
                'choices' => array(
                    'choices.proposition_contract_artist.yes' => true,
                    'choices.proposition_contract_artist.no' => false,
                ),
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'hall-choice'],
            ])
            ->add('artist', Select2EntityType::class, [
                'required' => true,
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
                    new Length(['min' => 10, 'minMessage' => 'proposition_contract_artist.reason.min'])
                ],
            ))
            ->add('nb_expected', IntegerType::class, array(
                'label' => 'labels.proposition_contract_artist.nb_expected',
                'required' => true,
                'attr' => array('min' => 0),
                'constraints' => [
                    new NotBlank(),
                    new Range(array(
                        'min' => 0,
                        'minMessage' => 'proposition_contract_artist.nb_expected.min_range',
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
                'widget' => 'choice',
                'years' => range(date('Y'), date('Y')+2),
                'constraints' => [
                    new NotBlank(),
                    new Assert\GreaterThanOrEqual('today')
                ],
            ))
            ->add('period_end_date', DateType::class, array(
                'label' => 'labels.proposition_contract_artist.period_end_date',
                'placeholder' => array(
                    'year' => 'placeholders.proposition_contract_artist.year',
                    'month' => 'placeholders.proposition_contract_artist.month',
                    'day' => 'placeholders.proposition_contract_artist.day',
                ),
                'years' => range(date('Y'), date('Y')+2),
                'required' => false,
            ))
            ->add('day_commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contract_artist.day_commentary',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 10, 'minMessage' => 'proposition_contract_artist.day_commentary.min'])
                ],
            ))
            ->add('commentary', TextareaType::class, array(
                'label' => 'labels.proposition_contract_artist.commentary',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 10, 'minMessage' => 'proposition_contract_artist.commentary.min'])
                ],
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.proposition_contract_artist.submit',
                'attr' => ['class' => 'btn btn-primary submitButton'],
            ));
    }

    public function validate(PropositionContractArtist $propositionContractArtist, ExecutionContextInterface $context)
    {
        if ($propositionContractArtist->radioHall == false) {
            $propositionContractArtist->setPropositionHall(null);
            if($propositionContractArtist->getProvince() == null){
                $context->buildViolation("proposition_contract_artist.province.null")
                    ->atPath('province')
                    ->addViolation();
            }
        } elseif ($propositionContractArtist->radioHall == true) {
            $this->validatePropositionHall($propositionContractArtist->getPropositionHall(), $context);
        } else {
            if($propositionContractArtist->getProvince() == null){
                $context->buildViolation("proposition_contract_artist.province.null")
                    ->atPath('province')
                    ->addViolation();
            }
            $this->validatePropositionHall($propositionContractArtist->getPropositionHall(), $context);
        }
        if ($propositionContractArtist->radioArtist == true) {
            $propositionContractArtist->setPropositionArtist(null);
            if($propositionContractArtist->getArtist() == null){
                $context->buildViolation("proposition_contract_artist.artist.null")
                    ->atPath('artist')
                    ->addViolation();
            }
        } elseif ($propositionContractArtist->radioArtist == false) {
            $this->validatePropositionArtist($propositionContractArtist->getPropositionArtist(), $context);
        } else {
            if($propositionContractArtist->getArtist() == null){
                $context->buildViolation("proposition_contract_artist.artist.null")
                    ->atPath('artist')
                    ->addViolation();
            }
            $this->validatePropositionArtist($propositionContractArtist->getPropositionArtist(), $context);
        }

        if($propositionContractArtist->getPeriodEndDate() != null && $propositionContractArtist->getPeriodEndDate()<= $propositionContractArtist->getPeriodStartDate()){
            $context->buildViolation("proposition_contract_artist.end_date.not_valable")
                ->atPath('period_end_date')
                ->addViolation();
        }
    }

    private function validatePropositionArtist(PropositionArtist $propositionArtist, ExecutionContextInterface $context)
    {
        if ($propositionArtist->getArtistname() == NULL || strlen(trim($propositionArtist->getArtistname())) == 0) {
            $context->buildViolation("proposition_contract_artist.artistname.empty")
                ->atPath('propositionArtist.artistname')
                ->addViolation();
        }
    }

    private function validatePropositionHall(PropositionHall $propositionHall, ExecutionContextInterface $context)
    {
        if ($propositionHall->getName() == NULL || strlen(trim($propositionHall->getName())) == 0) {
            $context->buildViolation("proposition_hall.name.empty")
                ->atPath('propositionHall.name')
                ->addViolation();
        }
        if ($propositionHall->getContactEmail() == NULL || strlen(trim($propositionHall->getContactEmail())) == 0) {
            $context->buildViolation("proposition_hall.contact_email.empty")
                ->atPath('propositionHall.contact_email')
                ->addViolation();
        }
        if ($propositionHall->getContactPhone() == NULL || strlen(trim($propositionHall->getContactPhone())) == 0) {
            $context->buildViolation("proposition_hall.contact_phone.empty")
                ->atPath('propositionHall.contact_phone')
                ->addViolation();
        }
        if ($propositionHall->getProvince() == NULL) {
            $context->buildViolation("proposition_contract_artist.province.null")
                ->atPath('propositionHall.province')
                ->addViolation();
        };
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionContractArtist',
            'csrf_protection' => false,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            )
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_contract_artist';
    }

}