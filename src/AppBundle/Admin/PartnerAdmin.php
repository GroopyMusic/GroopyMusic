<?php

namespace AppBundle\Admin;

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
            ->addIdentifier('name')
            ->add('comment')
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
            ->add('name')
            ->add('type')
            ->add('website')
            ->add('comment')
            ->end()
            ->with('Point de contact')
                ->add('contact_person')
            ->end()
            ->with('Adresse')
                ->add('address')
            ->end()
        ;

        if($this->getSubject() instanceof Hall) {
            $show
                ->add('capacity')
                ->add('step', null, array(
                'route' => array('name' => 'show'),
                ))
            ;
        }
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->add('name')
            ->add('website')
            ->add('comment')
            ->end()
            ->with('Point de contact')
                ->add('contact_person', 'sonata_type_collection', array(
                        'by_reference' => false,
                    ), array(
                        'edit'            => 'inline',
                        'inline'          => 'table',
                        'sortable'        => 'position',
                        'link_parameters' => array( 'context' => 'define context from which you want to select media or else just add default' ),
                        'admin_code'      => ContactPersonAdmin::class,
                    )
                )
            ->end()
            ->with('Adresse')
                ->add('address', 'sonata_type_admin')
            ->end()
        ;

        if ($subject instanceof Hall) {
            $formMapper
                ->add('capacity')
                ->add('step')
            ;
        }
    }

}