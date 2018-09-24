<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\YBContractArtist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YBContractArtistCrowdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var YBContractArtist $campaign */
        $campaign = $options['data'];

        if($campaign->isPending()) {
            $builder->add('validate', SubmitType::class, array(
                'label' => "Valider l'événement",
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ));

            $state = $campaign->getState();

            if($state == $campaign::STATE_PENDING) {
                $builder->add('refund', SubmitType::class, array(
                    'label' => 'Annuler l\'événement',
                    'attr' => [
                        'class' => 'btn btn-danger'
                    ]
                ));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_crowd_type';
    }
}
