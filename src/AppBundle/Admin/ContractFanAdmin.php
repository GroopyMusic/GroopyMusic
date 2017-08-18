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
            ->add('user')
            ->add('contractArtist', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('paid', 'boolean')
            ->add('cart', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('_action', null, array(
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
            ->add('user')
            ->add('cart')
            ->add('paid', 'boolean')
            ->add('contractArtist')
            ->add('purchases')
        ;
    }
}