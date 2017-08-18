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
                'route' => array('name' => 'show'),
            ))
            ->add('confirmed')
            ->add('paid')
            ->add('amount')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper) {
        $showMapper
            ->add('user', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('confirmed')
            ->add('paid')
            ->add('amount')
            ->add('contracts', null, array(
                'route' => array('name' => 'show'),
            ))
        ;
    }

}