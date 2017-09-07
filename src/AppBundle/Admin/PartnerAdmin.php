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
                'Commentaire interne'
            ))
            ->add('type')
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
                ->add('contact_persons', null, array(
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
        $subject = $this->getSubject();

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
                ->add('contact_persons', 'sonata_type_native_collection', array(
                    'required' => true,
                    'label' => 'Personnes de contact',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => 'entity',
                    'entry_options' => array(
                        'class' => ContactPerson::class,
                    )
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