<?php

namespace XBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use XBundle\Entity\Project;
use XBundle\Entity\XContractFan;
use XBundle\Entity\XPurchase;
use XBundle\Form\XPurchaseType;

class XContractFanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $has_parent = $options['has_parent'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($has_parent) {
            $contribution = $event->getData();
            $builder = $event->getForm();
            $builder
                ->add('purchases', CollectionType::class, array(
                    'label' => false,
                    'allow_add' => false,
                    'entry_type' => XPurchaseType::class,
                    'entry_options' => [
                        'project' => $contribution->getProject(),
                    ],
                ))
            ;
            if(!$has_parent)
                $builder
                    ->add('submit', SubmitType::class, array(
                        'label' => 'Valider',
                        'attr' => ['class' => 'btn btn-primary submit-cart'],
                    ));
        });
    }

    public function validate(XContractFan $contribution, ExecutionContextInterface $context)
    {
        if ($contribution->getCart() == null && $contribution->getProductsQuantity() == 0) {
            $context->addViolation('contractfan.quantity_min');
        }

        foreach($contribution->getPurchases() as $purchase) {
            /** @var XPurchase $purchase */
            if($purchase->getProduct()->getFreePrice() && $purchase->getFreePrice() < $purchase->getProduct()->getMinimumPrice()) {
                $context->addViolation('contractfan.free_price_min');
            }
            $purchasable = $purchase->getProduct()->getMaxAmountPerPurchase();
            if($purchase->getQuantity() > $purchasable) {
                $context->addViolation("Vous ne pouvez pas commander plus de " . $purchasable . " exemplaires de \"" . $purchase->getProduct()->getName() . "\".");
            }
        }
        /*if($contractFan->getCounterPartsQuantityOrganic() > $contract_artist->getMaxCounterParts())
            $context->addViolation("Il n'y a plus que " . $contract_artist->getMaxCounterParts() . " tickets disponibles, toutes catégories confondues. Veuillez réduire le nombre de tickets commandés");*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => XContractFan::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'has_parent' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'xbundle_xcontract_fan_type';
    }

}
