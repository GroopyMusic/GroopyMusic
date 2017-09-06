<?php

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PhaseAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('num')
            ->add('steps')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('getName', null, array(
                'label' => 'Nom'
            ))
            ->add('num')
            ->add('steps')
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('translations', TranslationsType::class)
            ->add('num')
            ->add('steps')
        ;
    }

}