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
            ->add('deleted', 'boolean', array(
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
                ->add('category', null , array(
                    'label' => 'Catégorie',
                ))
                ->add('dateEnd', null, array(
                    'label' => 'Date de clôture du financement partipatif',
                    'format' => 'd/m/Y',
                    'locale' => 'fr'
                ))
            ->end()
            ->with('Description')
                ->add('description', null, array(
                    'label' => 'A propos du projet',
                    'template' => 'XBundle:Admin:description.html.twig'
                ))
                ->add('motivations', null, array(
                    'label' => 'Motivations',
                    'template' => 'XBundle:Admin:motivations.html.twig'
                ))
                ->add('thresholdPurpose', null , array(
                    'label' => 'Objectif du financement',
                    'template' => 'XBundle:Admin:threshold_purpose.html.twig'
                ))
            ->end()
            ->with('Financement participatif')
                ->add('noThreshold', 'boolean', array(
                    'label' => 'Pas de seuil de validation'
                ))
                ->add('collectedAmount', null, array(
                    'label' => 'Montant récolté'
                ))
                ->add('threshold', null, array(
                    'label' => 'Montant à atteindre'
                ))
                ->add('products', null, array(
                    'label' => 'Articles mis en vente',
                    'template' => 'XBundle:Admin:products.html.twig',
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