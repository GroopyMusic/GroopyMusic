<?php

namespace AppBundle\Admin;

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
            ->add('type', null, array(
                'label' => 'Type',
            ) )
            ->add('_action', null, array(
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
                ->add('description', null, array(
                    'label' => 'Description publique',
                ))
                ->add('website', null, array(
                    'label' => 'Site Web',
                ))
                ->add('comment', null, array(
                    'label' => 'Commentaire (interne)',
                ))
                ->add('contactpersons_list', null, array(
                    'associated_property' => 'contact_person',
                    'label' => 'Personnes de contact',
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
                ->add('description', null, array(
                    'label' => 'Description publique',
                    'required' => false,
                ))
                ->add('website', null, array(
                    'label' => 'Site Web',
                    'required' => false,
                ))
                ->add('comment', null, array(
                    'label' => 'Commentaire (interne)',
                    'required' => false,
                ))
                ->add('contactpersons_list', 'sonata_type_collection', array(
                    'label' => false,
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