<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PaymentAdmin extends BaseAdmin
{
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
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

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->add('refund', $this->getRouterIdParameter().'/refund');
    }

}