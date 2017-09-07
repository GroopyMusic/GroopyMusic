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
                'label' => 'Phase',
                'route' => array('name' => 'show'),
            ))
            ->add('type', null, array(
                'label' => 'Type',
            ))
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
            ->add('num', 'integer', array(
                'label' => "Numéro d'ordre dans la phase",
            ))
            ->add('type', null, array(
                'label' => 'Type',
            ))
            ->add('phase', null, array(
                'label' => 'phase',
            ))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('num', 'integer', array(
                'required' => true,
                'label' => 'Numéro d\'ordre dans la phase',
            ))
            ->add('type', 'entity', array(
                'label' => 'Type',
                'class' => 'AppBundle:StepType'
            ))
            ->add('phase', 'entity', array(
                'label' => 'Phase',
                'class' => 'AppBundle:Phase'
            ))
            ->end()
            ->with('Champs traductibles')
                ->add('translations', TranslationsType::class, array(
                    'label' => false,
                    'fields' => [
                        'name' => [
                            'label' => 'Nom',
                        ],
                        'description' => [
                            'label' => 'Description',
                        ],
                    ],
                ))
            ->end()
        ;
    }

}