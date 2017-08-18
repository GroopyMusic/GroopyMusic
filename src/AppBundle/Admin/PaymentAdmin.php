<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PaymentAdmin extends BaseAdmin
{
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
                'route' => array('name' => 'show'),
            ))
            ->add('contractArtist', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('date')
            ->add('chargeId')
            ->add('amount')
            ->add('refunded')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'refund' => array(
                        'template' => 'AppBundle:Admin:icon_refund_payment.html.twig'
                    ),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper) {
        $showMapper
            ->add('user', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('date')
            ->add('chargeId')
            ->add('amount')
            ->add('refunded')
            ->add('contractArtist', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('contractFan', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('asking_refund')
        ;
    }
}