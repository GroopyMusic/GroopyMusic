<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ContractFanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contractfan = $builder->getData();

        $builder
            ->add('purchases', CollectionType::class, array(
                'label' => false,
                'allow_add' => false,
                'entry_type' => PurchaseType::class,
                'entry_options' => [
                    'contract_artist' => $contractfan->getContractArtist(),
                ],
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Valider la commande',
                'attr' => ['class' => 'btn btn-primary'],
            ))
        ;
    }

    public function validate(ContractFan $contractFan, ExecutionContextInterface $context) {
        if($contractFan->getCounterPartsQuantity() == 0) {
            $context->addViolationAt('purchases', "Pour poursuivre, il faut commander au moins une place de concert !");
        }
        if($contractFan->getCounterPartsQuantity() > $contractFan->getContractArtist()->getStep()->getMaxTickets() - $contractFan->getContractArtist()->getTicketsSold()) {
            $context->addViolationAt('purchases', "Il n'y a pas autant de places disponibles !!");
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContractFan::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_contract_fan_type';
    }
}
