<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PaymentAdmin extends BaseAdmin
{
    protected $perPageOptions = array(16, 32, 64, 128, 192, 1000000);

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
            ->add('refund', $this->getRouterIdParameter().'/refund')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('displayName', null, array(
                'label' => 'Acheteur',
            ))
            ->add('user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtistsText', null, array(
                'label' => 'Evenements',
            ))
            ->add('date', null, array(
                'format' => 'd/m/y H:i:s',
            ))
            ->add('chargeId', null, array(
                'label' => 'Identifiant paiement Stripe',
            ))
            ->add('amount', null, array(
                'label' => 'Montant',
            ))
            ->add('refunded', null, array(
                'label' => 'Remboursé'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'refund' => array(
                        'template' => 'AppBundle:Admin/Payment:icon_refund.html.twig'
                    ),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper) {
        $showMapper
            ->add('displayName', null, array(
                'label' => 'Acheteur',
            ))
            ->add('user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtistsText', null, array(
                'label' => 'Evenements',
            ))
            ->add('date', null, array(
                'format' => 'd/m/y H:i:s',
            ))
            ->add('chargeId', null, array(
                'label' => 'Identifiant paiement Stripe',
            ))
            ->add('amount', null, array(
                'label' => 'Montant',
            ))
            ->add('refunded', null, array(
                'label' => 'Remboursé'
            ))
            ->add('asking_refund', null, array(
                'label' => 'Demandes de remboursement',
            ))
        ;
    }

    public function getExportFields() {
        return [
            '#' => 'id',
            'Date' => 'date',
            'Ticked-it' => 'YB',
            'Stripe ID' => 'chargeId',
            'Remboursé' => 'refunded',
            'Nom acheteur' => 'displayName',
            'Evénement(s)' => 'contractArtistsText',
            'Achats' => 'purchasesText',
            'Montant' => 'amount',
            'Nombre de tickets obtenus' => 'counterPartsQuantity',
            'dont payés' => 'counterPartsQuantityOrganic',
            'dont promotion' => 'counterPartsQuantityPromotional'
        ];
    }
}