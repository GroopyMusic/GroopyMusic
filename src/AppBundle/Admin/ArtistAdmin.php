<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ArtistAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('artistname')
            ->add('_action', null, array(
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
            ->add('artistname')
            ->add('phase')
            ->add('genres')
            ->add('artists_user', null, array(
                'associated_property' => 'userToString'
            ))
            ->add('short_description')
            ->add('biography')
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('short_description')
            ->add('biography')
        ;
    }
}