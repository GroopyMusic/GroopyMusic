<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class CartAdmin extends BaseAdmin
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
            ->add('user', null, array(
                'label' => 'Membre',
                'route' => array('name' => 'show'),
            ))
            ->add('confirmed', null, array(
                'label' => 'Confirmé',
            ))
            ->add('paid', null, array(
                'label' => 'Payé',
            ))
            ->add('amount', null, array(
                'label' => 'Montant',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper) {
        $showMapper
            ->with('Infos')
                ->add('user', null, array(
                    'label' => 'Membre',
                    'route' => array('name' => 'show'),
                ))
                ->add('amount', null, array(
                    'label' => 'Montant'
                ))
                ->add('contracts', null, array(
                    'label' => 'Contrats pour l\'utilisateur',
                    'route' => array('name' => 'show'),
                ))
            ->end()
            ->with('État')
                ->add('confirmed', null, array(
                    'label' => 'Confirmé',
                ))
                ->add('paid', null, array(
                    'label' => 'Payé',
                ))
            ->end()
        ;
    }

}