<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class StepAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('getName', null, array(
                'label' => 'Nom',
                'route' => array('name' => 'show'),
            ))
            ->add('phase', null, array(
                'label' => 'Phase',
                'route' => array('name' => 'show'),
            ))
            ->add('getDescription', null, array(
                'label' => 'Description'
            ))
            ->add('approximate_capacity', null, array(
                'label' => 'Capacité approximative',
            ))
            ->add('visible', null, array(
                'label' => 'Visible sur la plateforme',
                'editable' => true,
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Infos générales')
                ->add('getName', null, array(
                    'required' => true,
                    'label' => 'Nom'
                ))
                ->add('getDescription', 'text', array(
                    'required' => true,
                    'label' => 'Description',
                ))
                ->add('phase', null, array(
                    'required' => true,
                    'label' => 'phase',
                ))
                ->add('num', 'integer', array(
                    'required' => true,
                    'label' => "Numéro d'ordre dans la phase",
                ))
                ->add('visible', null, array(
                    'label' => 'Visible sur la plateforme',
                ))
            ->end()
            ->with('Infos par rapport aux concerts & crowdfundings')
                ->add('approximate_capacity', null, array(
                    'required' => true,
                    'label' => 'Capacité approximative',
                ))
                ->add('delay', null, array(
                    'required' => true,
                    'label' => 'Délai de confirmation par défaut (peut varier en fonction des salles)',
                ))
                ->add('delay_margin', null, array(
                    'required' => true,
                    'label' => 'Nombre de jours ouverts pour le choix de date après le délai de confirmation (= fenêtre de shotgun pour les artistes)'
                ))
                ->add('deadline_duration', null, array(
                    'required' => true,
                    'label' => 'Nombre de jours avant la validation',
                ))
                ->add('min_tickets', null, array(
                    'required' => true,
                    'label' => 'Nombre de tickets min à vendre pour réussite',
                ))
                ->add('max_tickets', null, array(
                    'required' => true,
                    'label' => 'Nombre de tickets max à vendre',
                ))
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Champs traductibles')
            ->add('translations', TranslationsType::class, array(
                'label' => false,
                'fields' => [
                    'name' => [
                        'label' => 'Nom',
                    ],
                    'description' => [
                        'label' => 'Description',
                    ],
                ],
            ))
            ->end()

            ->with('Visibilité')
                ->add('visible', null, array(
                    'label' => 'Visible sur la plateforme',
                ))
            ->end()

            ->with('Phase')
                ->add('phase', null, array(
                    'required' => true,
                    'label' => 'Phase',
                ))
                ->add('num', 'integer', array(
                    'required' => true,
                    'label' => "Numéro d'ordre dans la phase",
                ))
            ->end()


            ->with('Infos par rapport aux concerts & crowdfundings')
                ->add('approximate_capacity', null, array(
                    'required' => true,
                    'label' => 'Capacité approximative',
                ))
                ->add('delay', null, array(
                    'required' => true,
                    'label' => 'Délai de confirmation par défaut (peut varier en fonction des salles)',
                ))
                ->add('delay_margin', null, array(
                    'required' => true,
                    'label' => 'Nombre de jours ouverts pour le choix de date après le délai de confirmation (= fenêtre de shotgun pour les artistes)',
                ))
                ->add('deadline_duration', null, array(
                    'required' => true,
                    'label' => 'Nombre de jours avant la validation',
                ))
                ->add('min_tickets', null, array(
                    'required' => true,
                    'label' => 'Nombre de tickets min à vendre pour réussite',
                ))
                ->add('max_tickets', null, array(
                    'required' => true,
                    'label' => 'Nombre de tickets max à vendre',
                ))
            ->end()
        ;
    }

}