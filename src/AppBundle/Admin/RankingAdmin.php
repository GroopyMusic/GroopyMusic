<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class RankingAdmin extends BaseAdmin
{
    protected $baseRoutePattern = 'ranking';
    protected $baseRouteName = 'ranking';

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->add('compute', '/compute')
            ->add('displayMore', '/displayMore')
        ;
    }
}