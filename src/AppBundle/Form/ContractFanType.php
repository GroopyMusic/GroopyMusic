<?php

namespace AppBundle\Form;

use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\ReductionReward;
use AppBundle\Entity\User_Reward;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('user_rewards', EntityType::class, array(
                'class' => User_Reward::class,
                'choices' => $options['user_rewards'],
                'label' => 'labels.contractfan.rewards',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'js-example-basic-multiple user-rewards-select'],
                'choice_attr' => function (User_Reward $val, $key, $index) {
                    if ($val->getReward() instanceof ReductionReward) {
                        return ['class' => 'reduction'];
                    } else if ($val->getReward() instanceof ConsomableReward) {
                        return ['class' => 'consomable'];
                    } else if ($val->getReward() instanceof InvitationReward) {
                        return ['class' => 'invitation'];
                    }
                    return null;
                }
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.contractfan.submit',
                'attr' => ['class' => 'btn btn-primary'],
            ));
    }

    public function validate(ContractFan $contractFan, ExecutionContextInterface $context)
    {
        if ($contractFan->getCounterPartsQuantity() == 0) {
            $context->addViolation('contractfan.quantity_min');
        }
        if ($contractFan->getCounterPartsQuantity() > $contractFan->getContractArtist()->getStep()->getMaxTickets() - $contractFan->getContractArtist()->getTicketsSold()) {
            $context->addViolation('contractfan.quantity_max');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ContractFan::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'user_rewards' => null
        ));
        $resolver->setRequired('entity_manager');
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_contract_fan_type';
    }

}
