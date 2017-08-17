<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PaymentAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->add('refund', $this->getRouterIdParameter().'/refund')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list->add('date')
            ->add('chargeId')
            ->add('amount')
            ->add('refunded')
            ->add('_action', null, array(
                'actions' => array(
                    'refund' => array(
                        'template' => 'AppBundle:Admin:icon_refund_payment.html.twig'
                    )
                )))
        ;
    }

}