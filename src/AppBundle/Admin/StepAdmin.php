<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
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
            ->addIdentifier('getName', null, array(
                'label' => 'Nom',
                'route' => array('name' => 'show'),
            ))
            ->add('phase', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('type')
            ->add('getDescription', null, array(
                'label' => 'Description'
            ))
            ->add('_action', 'actions', array(
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
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('getDescription', 'text', array(
                'label' => 'Description',
            ))
            ->add('num', 'integer')
            ->add('type')
            ->add('phase')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('translations', TranslationsType::class)
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