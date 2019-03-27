<?php

namespace AppBundle\Form;

use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractArtistPot;
use AppBundle\Entity\ContractArtistSales;
use AppBundle\Entity\Purchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BaseContractArtist $contract_artist */
        $contract_artist = $options['contract_artist'];

        $builder->add('quantity', NumberType::class, array(
            'attr' => [
                'class' => 'quantity',
            ],
            'label' => false,
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($contract_artist) {
            $purchase = $event->getData();
            if(!empty($purchase->getCounterPart()->getPotentialArtists())) {
                $event->getForm()->add('artists', Select2EntityType::class, array(
                    'required' => false,
                    'label' => false,
                    'multiple' => true,
                    'remote_route' => 'select2_counterpart_artists',
                    'remote_params' => ['counterpart' => $purchase->getCounterPart()->getId()],
                    'class' => 'AppBundle\Entity\Artist',
                    'primary_key' => 'id',
                ));
            }
            if($contract_artist instanceof ContractArtistSales || $contract_artist instanceof ContractArtistPot || $purchase->getCounterPart()->getFreePrice()) {
                $event->getForm()->add('free_price_value', NumberType::class, array(
                    'attr' => [
                        'class' => 'free-price-value',
                    ],
                    'label' => false,
                ));
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Purchase::class,
            'contract_artist' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_purchase_type';
    }
}
