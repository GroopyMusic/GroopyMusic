<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class StepAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('phase', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('type')
            ->add('description')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('description', 'text')
            ->add('num', 'integer')
            ->add('type')
            ->add('phase')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name')
            ->add('description', 'text')
            ->add('num', 'integer')
            ->add('type', 'entity', array(
                'class' => 'AppBundle:StepType'
            ))
            ->add('phase', 'entity', array(
                'class' => 'AppBundle:Phase'
            ))
        ;
    }

}