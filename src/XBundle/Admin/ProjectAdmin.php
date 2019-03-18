<?php

namespace XBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use AppBundle\Entity\Artist;

class ProjectAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
            ->add('validate', $this->getRouterIdParameter().'/validate')
            ->add('refuse', $this->getRouterIdParameter().'/refuse');
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('title', null, array(
                'label' => 'Titre du projet'
            ))
            ->add('artist', null, array(
                'label' => 'Artiste associé'
            ))
            ->add('validated', null, array(
                'label'=> 'Validé'
            ))
            ->add('deleted', null, array(
                'label' => 'Supprimé'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'validate' => array(
                        'template' => 'XBundle:Admin:icon_validate_project.html.twig'
                    ),
                    'refuse' => array(
                        'template' => 'XBundle:Admin:icon_refuse_project.html.twig'
                    )
                ) 
            ))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Infos générales')
                ->add('title', null, array(
                    'label' => 'Titre du projet'
                ))
                ->add('artist', null, array(
                    'label' => 'Artiste associé'
                ))
                ->add('tag', null , array(
                    'label' => 'Catégorie',
                    'associated_property' => 'name'
                ))
                ->add('description', null, array(
                    'label' => 'A propos du projet'
                ))
                ->add('motivations', null, array(
                    'label' => 'Motivations'
                ))
                ->add('thresholdPurpose', null , array(
                    'label' => 'Objectif du financement'
                ))
                ->add('dateEnd', null, array(
                    'label' => 'Date de clôture du financement partipatif'
                ))
            ->end()
            ->with('Financement participatif')
                ->add('collectedAmount', null, array(
                    'label' => 'Montant récolté'
                ))
                ->add('threshold', null, array(
                    'label' => 'Seuil de validation'
                ))
            ->end()
            ->with('État')
                ->add('validated', 'boolean', array(
                    'label' => 'Validé par un administrateur Un-Mute'
                ))
                ->add('deleted', 'boolean', array(
                    'label' => 'Supprimé ou refusé'
                ))
                ->add('successful', 'boolean', array(
                    'label' => 'Réussite'
                ))
                ->add('failed', 'boolean', array(
                    'label' => 'Échec'
                ))
                ->add('refunded', 'boolean', array(
                    'label' => 'Remboursé'
                ))
            ->end()
        ;
    }

}