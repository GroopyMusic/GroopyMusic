<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
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
            ->add('artistname', null, array(
                'label' => "Nom de l'artiste",
                'required' => true,
            ))
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
            ->add('artistname')
            ->add('phase')
            ->add('genres')
            ->add('artists_user', null, array(
                'associated_property' => 'userToString'
            ))
            ->add('getShortDescription', null, array(
                'label' => 'Description courte',
            ))
            ->add('getBiography', null, array(
                'label' => 'Biographie',
            ))
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('translations', TranslationsType::class)
        ;
    }
}