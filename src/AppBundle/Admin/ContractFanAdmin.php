<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ContractFanAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create');
    }

    public function configureListFields(ListMapper $list)
    {
        $list->add('id')
            ->add('user')
            ->add('contractArtist')
        ;
    }
}