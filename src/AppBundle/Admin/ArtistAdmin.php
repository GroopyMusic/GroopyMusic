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
            ->add('artistname', null, array(
                'label' => "Nom de l'artiste",
            ))
            ->add('phase', null, array(
                'label' => "Phase de l'artiste",
            ))
            ->add('genres', null, array(
                'label' => "Genres musicaux",
            ))
            ->add('artists_user', null, array(
                'label' => 'Propriétaires',
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
            ->with('Champs traductibles')
                ->add('translations', TranslationsType::class, array(
                    'label' => false,
                    'fields' => [
                        'short_description' => [
                            'label' => 'Description courte',
                        ],
                        'biography' => [
                            'label' => 'Biographie',
                        ],
                    ],
                ))
            ->end()
        ;
    }
}