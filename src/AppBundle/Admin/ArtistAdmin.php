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
            ->add('validate', $this->getRouterIdParameter().'/validate')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('artistname', null, array(
                'label' => "Nom de l'artiste",
            ))
            ->add('validated', null, array(
                'label' => 'Vérifié',
            ))
            ->add('visible', null, array(
                'label' => 'Visible sur la plateforme',
                'editable' => true,
            ))
            ->add('isActive', 'boolean', array(
                'label' => 'Actif',
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'validate' => array(
                        'template' => 'AppBundle:Admin/Artist:icon_validate.html.twig'
                    ),
                )
            ))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Généralités')
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
                    'template' => 'AppBundle:Admin/Artist:owners.html.twig',
                ))
                ->add('getShortDescription', null, array(
                    'label' => 'Description courte',
                ))
                ->add('getBiography', null, array(
                    'label' => 'Biographie',
                ))
                ->add('phone', null, array(
                    'label' => 'Téléphone',
                ))
            ->end()
            ->with('Etat')
                ->add('active', 'boolean', array(
                    'label' => 'Actif',
                ))
                ->add('validated', 'boolean', array(
                    'label' => 'Vérifié par un administrateur Un-Mute',
                ))
                ->add('visible', 'boolean', array(
                    'label' => 'Visible sur la plateforme',
                ))
            ->end()
            ->with('Médias')
                ->add('website', null, array(
                    'label' => 'Site Web',
                ))
                ->add('facebook', null, array(
                    'label' => 'Facebook',
                ))
                ->add('twitter', null, array(
                    'label' => 'Twitter',
                ))
                ->add('spotify', null, array(
                    'label' => 'Spotify',
                ))
                ->add('soundcloud', null, array(
                    'label' => 'SoundCloud',
                ))
                ->add('bandcamp', null, array(
                    'label' => 'Bandcamp',
                ))
                ->add('profilepic', null, array(
                    'label' => 'Photo de profil',
                    'template' => 'AppBundle:Admin/Artist:pp.html.twig',
                ))
                ->add('photos', null, array(
                    'label' => 'Autres photos',
                    'template' => 'AppBundle:Admin/Artist:photos.html.twig',
                ))
                ->add('videos', null, array(
                    'label' => 'Vidéos',
                    'template' => 'AppBundle:Admin/Artist:videos.html.twig',
                ))
            ->end()
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

            ->with('Autres')
                ->add('visible', null, array(
                    'required' => false,
                    'label' => 'Laisser visible sur la plateforme par le grand public',
                ))
                ->add('deleted', null, array(
                    'required' => false,
                    'label' => "\"Supprimer\" l'artiste (il ne sera plus visible sur la plateforme NI accessible par ses gestionnaire)",
                ))
            ->end()
        ;
    }
}