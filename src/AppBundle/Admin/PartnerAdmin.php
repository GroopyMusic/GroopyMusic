<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\ContactPerson;
use AppBundle\Entity\Hall;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PartnerAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array(
                'label' => 'Nom',
            ))
            ->add('comment', null, array(
                'label' => 'Commentaire interne',
            ))
            ->add('getShortDescription', null, array(
                'label' => 'Description courte',
            ))
            ->add('type', null, array(
                'label' => 'Type',
            ))
            ->add('visible', null, array(
                'label' => 'Visible sur la plateforme',
                'editable' => true,
            ))
            ->add('ephemeral', null, array(
                'label' => 'Éphémère',
                'editable' => true,
            ))
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                    )
                )
            )
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Données générales du partenaire')
                ->add('name', null, array(
                    'label' => 'Nom',
                ))
                ->add('getDescription', null, array(
                    'label' => 'Description publique',
                ))
                ->add('getShortDescription', null, array(
                    'label' => 'Description courte',
                ))
                ->add('website', null, array(
                    'label' => 'Site Web',
                ))
                ->add('comment', null, array(
                    'label' => 'Commentaire (interne)',
                ))
                ->add('visible', null, array(
                    'label' => 'Visible sur la plateforme',
                ))
                ->add('ephemeral', null, array(
                    'label' => 'Éphémère',
                ))
                ->add('contactpersons_list', null, array(
                    'associated_property' => 'contact_person',
                    'label' => 'Personnes de contact',
                    'route' => ['name' => 'nonexistent-route'] // TODO make this more usable by returning the route to the contact person admin...
                ))
                ->add('address', 'sonata_type_admin', array(
                    'label' => 'Adresse',
                ))
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Données générales du partenaire')
                ->add('name', null, array(
                    'label' => 'Nom',
                    'required' => true,
                ))
                ->add('translations', TranslationsType::class, array(
                    'label' => 'Champs traductibles',
                    'fields' => [
                        'short_description' => [
                            'label' => 'Description courte (max 255 caractères)',
                        ],
                        'description' => [
                            'label' => 'Description détaillée',
                            'required' => false,
                        ]
                    ],
                ))
                ->add('website', null, array(
                    'label' => 'Site Web',
                    'required' => false,
                ))
                ->add('comment', null, array(
                    'label' => 'Commentaire (interne)',
                    'required' => false,
                ))
                ->add('visible', null, array(
                    'label' => 'Visible sur la plateforme',
                ))
                ->add('ephemeral', null, array(
                    'label' => 'Doit être considéré comme éphémère',
                ))
                ->add('contactpersons_list', 'sonata_type_collection', array(
                    'label' => 'Personnes de contact',
                    'by_reference' => false,
                ), array(
                        'edit'            => 'inline',
                        'inline'          => 'table',
                        'sortable'        => 'position',
                        'admin_code'      => PartnerContactPersonAdmin::class,
                    )
                )
            ->end()
            ->with('Adresse')
                ->add('address', 'sonata_type_admin', array(
                    'required' => true,
                    'label' => 'Adresse'
                ))
            ->end()
        ;
    }

}