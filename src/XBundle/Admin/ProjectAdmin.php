<?php

namespace XBundle\Admin;

use AppBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;


class ProjectAdmin extends BaseAdmin
{

    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
            ->add('validate', $this->getRouterIdParameter().'/validate')
            ->add('refuse', $this->getRouterIdParameter().'/refuse')
            ->add('products', $this->getRouterIdParameter().'/product/list');
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
            ->add('deletedAt', 'boolean', array(
                'label' => 'Supprimé'
            ))
            ->add('successful', null, array(
                'label' => 'Réussi'
            ))
            ->add('failed', null, array(
                'label' => 'Échoué'
            ))
            ->add('refunded', null, array(
                'label' => 'Remboursé'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'validate' => array(
                        'template' => 'XBundle:Admin:icon_validate.html.twig'
                    ),
                    'refuse' => array(
                        'template' => 'XBundle:Admin:icon_refuse.html.twig'
                    ),
                    'products' => array(
                        'template' => 'XBundle:Admin:icon_products_project.html.twig'
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
                ->add('dateEvent', null, array(
                    'label' => "Date de l'évènement",
                    'format' => 'd/m/Y',
                    'locale' => 'fr'
                ))
                ->add('address', null, array(
                    'label' => "Lieu de l'évènement"
                ))
                ->add('description', null, array(
                    'label' => 'À propos du projet',
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
                ->add('coverpic', null , array(
                    'label' => 'Photo de couverture',
                    'template' => 'XBundle:Admin:coverpic.html.twig'
                ))
            ->end()
            ->with('Financement participatif')
                ->add('hasThreshold', 'boolean', array(
                    'label' => 'A de seuil de validation'
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
                ->add('validated', null, array(
                    'label' => 'Validé par un administrateur Un-Mute'
                ))
                ->add('deletedAt', 'boolean', array(
                    'label' => 'Supprimé ou refusé'
                ))
                ->add('successful', null, array(
                    'label' => 'Réussite'
                ))
                ->add('failed', null, array(
                    'label' => 'Échec'
                ))
                ->add('refunded', null, array(
                    'label' => 'Remboursé'
                ))
            ->end()
        ;
    }

}