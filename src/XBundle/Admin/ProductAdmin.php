<?php

namespace XBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use XBundle\Entity\Project;

class ProductAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
            ->add('validate', $this->getRouterIdParameter().'/validate')
            ->add('refuse', $this->getRouterIdParameter().'/refuse')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('name', null, array(
                'label' => 'Intitulé'
            ))
            ->add('project', null, array(
                'label' => 'Projet associé',
            ))
            ->add('validated', null, array(
                'label'=> 'Validé'
            ))
            ->add('deletedAt', 'boolean', array(
                'label' => 'Supprimé'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'validate' => array(
                        'template' => 'XBundle:Admin/Product:icon_validate_product.html.twig'
                    ),
                    'refuse' => array(
                        'template' => 'XBundle:Admin/Product:icon_refuse_product.html.twig'
                    )
                ) 
            ))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Infos générales')
                ->add('name', null, array(
                    'label' => 'Intitulé'
                ))
                ->add('project', null, array(
                    'label' => 'Projet associé',
                ))
                ->add('description', null , array(
                    'label' => 'Description'
                ))
                ->add('price', null, array(
                    'label' => 'Prix'
                ))
                ->add('supply', null, array(
                    'label' => 'Stock'
                ))
            ->end()
            ->with('État')
                ->add('validated', null, array(
                    'label' => 'Validé par un administrateur Un-Mute'
                ))
                ->add('deletedAt', 'boolean', array(
                    'label' => 'Supprimé ou refusé'
                ))
            ->end()
        ;
    }

}