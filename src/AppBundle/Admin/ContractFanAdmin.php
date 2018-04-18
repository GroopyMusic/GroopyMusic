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
            ->add('cart.user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtist', null, array(
                'label' => 'Crowdfunding',
                'route' => array('name' => 'show'),
            ))
            ->add('paid', 'boolean', array(
                'label' => 'Payé'
            ))
            ->add('cart', null, array(
                'label' => 'Panier correspondant',
                'route' => array('name' => 'show'),
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
            ->end()
            ->with('État')
                ->add('paid', 'boolean', array(
                    'label' => 'Payé',
                ))
                ->add('counterparts_sent', 'boolean', array(
                    'label' => 'Tickets envoyés',
                ))
            ->end()
        ;
    }
}