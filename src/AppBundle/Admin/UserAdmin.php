<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class UserAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('firstname', 'text')
            ->add('lastname', 'text')
            ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('lastname');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('firstname');
    }

    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        unset($actions['create']);

        return $actions;
    }
}