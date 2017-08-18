<?php

namespace AppBundle\Admin;

use AppBundle\Entity\ContractArtist_Artist;
use AppBundle\Entity\StepType;
use AppBundle\Form\ConcertPossibilityType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ContractArtistAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->add('refund', $this->getRouterIdParameter().'/refund')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('date')
            ->add('artist', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('step', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('date_end', 'datetime', array(
                'format' => 'd/m/Y',
            ))
            ->add('failed')
            ->add('successful')
            ->add('refunded')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'refund' => array(
                        'template' => 'AppBundle:Admin:icon_refund_contractartist.html.twig',
                    ),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('id')
            ->add('date')
            ->add('dateEnd')
            ->add('step', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('artist', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('motivations')
            ->add('payments', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('reminders')
            ->add('preferences')
            ->add('coartists_list')
            ->add('reality')
            ->add('collected_amount')
            ->add('failed')
            ->add('successful')
            ->add('cart_reminder_sent')
            ->add('refunded')
            ->add('contractsFan', null, array(
                'route' => array('name' => 'show'),
            ))
            ->add('asking_refund')
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('dateEnd')
            ->add('motivations')
            ->end();

        if($this->getSubject()->getStep()->getType()->getName() == StepType::TYPE_CONCERT) {
            $form
                ->with('Réalité')
                    ->add('reality', 'sonata_type_admin', array('required' => false), array(
                        'admin_code' => 'app.admin.concertpossibility',
                    ))
                ->end();
        }

        $form
            ->with('Premières parties')
            ->add( 'coartists_list', 'sonata_type_collection', array(
                'by_reference' => false,
            ), array(

                    'edit'            => 'inline',
                    'inline'          => 'table',
                    'sortable'        => 'position',
                    'link_parameters' => array( 'context' => 'define context from which you want to select media or else just add default' ),
                    'admin_code'      => 'app.admin.contractartistartist'
                )
            )
            ->end()
        ;

    }
}