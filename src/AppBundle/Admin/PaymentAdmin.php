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
            ->add('user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtist', null, array(
                'label' => 'Crowdfunding',
                'route' => array('name' => 'show'),
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
            ->add('user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtist', 'url', array(
                'label' => 'Event',
                'route' => [
                    'name' => 'artist_contract',
                    'parameters' => ['id' => $this->getSubject()->getContractArtist()->getId()]
                ],
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
            ->add('contractFan', null, array(
                'label' => 'Contreparties achetées',
                'route' => array('name' => 'show'),
            ))
            ->add('contractFan.ticket_sent', 'boolean', array(
                'label' => 'Tickets envoyés',
            ))
            ->add('refunded', null, array(
                'label' => 'Remboursé',
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
            'Stripe ID' => 'chargeId',
            'Remboursé' => 'refunded',
            '# Utilisateur' => 'user.id',
            'Nom utilisateur' => 'user.displayName',
            '# Evénement' => 'contractArtist.id',
            'Evénement' => 'contractArtist',
            'Montant' => 'amount',
            'Nombre de tickets obtenus' => 'counterPartsQuantity',
            'dont payés' => 'counterPartsQuantityOrganic',
            'dont promotion' => 'counterPartsQuantityPromotional'
        ];

    }
}