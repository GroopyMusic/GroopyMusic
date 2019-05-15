<?php

namespace XBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;


class ProductAdmin extends BaseAdmin
{

    public function configureRoutes(RouteCollection $collection)
    {
        if ($this->isChild()) {
            parent::configureRoutes($collection);
            $collection
                ->remove('delete')
                ->remove('create')
                ->remove('edit')
                ->add('validate', $this->getRouterIdParameter().'/validate')
                ->add('refuse', $this->getRouterIdParameter().'/refuse');
            return;
        }

        // This is the route configuration as a parent
        $collection->clear();
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('name', null, array(
                'label' => 'Intitulé'
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
                        'template' => 'XBundle:Admin:icon_validate.html.twig'
                    ),
                    'refuse' => array(
                        'template' => 'XBundle:Admin:icon_refuse.html.twig'
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
                ->add('freePrice', null, array(
                    'label' => 'Prix libre'
                ))
                ->add('supply', null, array(
                    'label' => 'Stock'
                ))
                ->add('productsSold', null, array(
                    'label' => 'Vendu'
                ))
                ->add('photo', null, array(
                    'label' => 'Photo',
                    'template' => 'XBundle:Admin/Product:photo.html.twig'
                ))
                ->add('options', null, array(
                    'label' => 'Option(s)',
                    'template' => 'XBundle:Admin/Product:options.html.twig'
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