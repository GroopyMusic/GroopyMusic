<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class SuggestionBoxAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('edit')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('displayName')
            ->add('date')
            ->add('object')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                )
            ))
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('name')
            ->add('lastname')
            ->add('email')
            ->add('date')
            ->add('mailCopy')
            ->add('object')
            ->add('message')
        ;
    }
}