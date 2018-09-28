<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ContractFanAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('date')
            ->add('displayName', null, array(
                'label' => 'Acheteur',
            ))
            ->add('cart.user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtist', null, array(
                'label' => 'Evenement',
                'route' => array('name' => 'show'),
            ))
            ->add('paid', 'boolean', array(
                'label' => 'Payé'
            ))
            ->add('amount', null, array(
                'label' => 'Montant'
            ))
            ->add('purchasesExport', null, array(
                'label' => 'Achats',
            ))
            ->add('ticketsExport', null, array(
                'label' => 'Tickets',
            ))
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                    )
                )
            )
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Infos')
                ->add('id')
                ->add('displayName', null, array(
                    'label' => 'Acheteur',
                ))
                ->add('email', null, array(
                    'label' => 'Email',
                ))
                ->add('cart.user', null, array(
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
                ->add('payment', null, array(
                    'label' => 'Paiement',
                    'route' => ['name' => 'show'],
                ))
                ->add('date', 'datetime', array(
                    'label' => 'Date de création',
                    'format' => 'd/m/y H:i:s',
                ))
                ->add('purchases', null, array(
                    'label' => 'Achats',
                    'route' => ['name' => 'show']
                ))
                ->add('cart', null, array(
                    'label' => 'Panier correspondant',
                    'route' => array('name' => 'show'),
                ))
            ->add('amount', null, array(
                'label' => 'Montant',
            ))
            ->end()
            ->with('État')
                ->add('paid', 'boolean', array(
                    'label' => 'Payé',
                ))
                ->add('counterparts_sent', 'boolean', array(
                    'label' => 'Tickets envoyés',
                ))
            ->end()
            ->with('Tickets')
                ->add('ticketsExport', null, array(
                    'label' => 'Tickets',
                ))
            ->end()
            ->with('Récompenses')
            ->add('user_rewards', null, array(
                'label' => 'Récompenses utilisées',
                'route' => array('name' => 'show'),
            ))
            ->add('ticket_rewards', null, array(
                'label' => 'Récompenses attribuées',
                'route' => array('name' => 'show'),
            ))
            ->end()
        ;
    }

    public function getExportFields()
    {
        return array_merge(parent::getExportFields(), [
            'id paiement' => 'paymentExport',
            'Evenement' => 'contractArtistExport',
            'Achats' => 'purchasesExport',
            'Acheteur' => 'displayName',
            'Membre' => 'userExport',
            'Tickets' => 'ticketsExport',
        ]);
    }
}