<?php

namespace AppBundle\Form;

use AppBundle\Entity\Artist;
use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractArtistPot;
use AppBundle\Entity\ContractArtistSales;
use AppBundle\Entity\Purchase;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                $event->getForm()->add('artist', Select2EntityType::class, array(
                    'required' => false,
                    'label' => false,
                    'multiple' => false,
                    'placeholder' => "Pas d'artiste favori",
                    'remote_route' => 'select2_counterpart_artists',
                    'remote_params' => ['counterpart' => $purchase->getCounterPart()->getId()],
//                    'query_builder' => function (EntityRepository $er) use ($purchase) {
//                        return $er->createQueryBuilder('a')
//                            ->where('a.id IN(:artistsIds)')
//                            ->orderBy('a.artistname', 'ASC')
//                            ->setParameter('artistsIds', array_values(array_map(function(Artist $artist) use ($purchase) { return $artist->getId();}, $purchase->getCounterPart()->getPotentialArtists())));
//                    },
                    'class' => 'AppBundle\Entity\Artist',
                ));
            }
            if($contract_artist instanceof ContractArtistSales || $contract_artist instanceof ContractArtistPot || $purchase->getCounterPart()->getFreePrice()) {
                $event->getForm()->add('free_price_value', NumberType::class, array(
                    'attr' => [
                        'class' => 'free-price-value counterpart-price',
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
