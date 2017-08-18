<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')

        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('displayName')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                )))
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('lastname')
            ->add('firstname')
            ->add('newsletter')
            ->add('credits')
            ->add('genres')
            ->add('payments')
            ->add('stripe_customer_id')
            ->add('carts')
            ->add('specialPurchases')
        ;
    }
}