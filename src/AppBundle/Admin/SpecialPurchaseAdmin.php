<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Route\RouteCollection;

class SpecialPurchaseAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
        ;
    }
}