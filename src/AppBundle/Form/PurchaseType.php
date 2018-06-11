<?php

namespace AppBundle\Form;

use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractArtistSales;
use AppBundle\Entity\Purchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var BaseContractArtist $contract_artist
         */
        $contract_artist = $options['contract_artist'];


            if($contract_artist instanceof ContractArtist) {
                $builder->add('quantity', NumberType::class, array(
                    'attr' => ['class' => 'quantity',
                        'min' => 0,
                        'max' => $contract_artist->getTotalNbAvailable(),
                    ],
                    'label' => false,
                ));
            }
        ;
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
