<?php

namespace AppBundle\Admin;

use AppBundle\Entity\BaseStep;
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
            ->add('validate', $this->getRouterIdParameter().'/validate')
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('date', 'date', array(
                'label' => 'Date de création',
                'format' => 'd/m/Y',
            ))
            ->add('artist', null, array(
                'label' => 'Artiste',
                'route' => array('name' => 'show'),
            ))
            ->add('step', null, array(
                'label' => 'Palier',
                'route' => array('name' => 'show'),
            ))
            ->add('date_end', 'date', array(
                'label' => 'Échéance',
                'format' => 'd/m/Y',
            ))
            ->add('failed', null, array(
                'label' => 'Échec',
            ))
            ->add('successful', null, array(
                'label' => 'Réussi',
            ))
            ->add('refunded', null, array(
                'label' => 'Remboursé',
            ))
            ->add('state', null, array(
                'label' => 'Etat'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'refund' => array(
                        'template' => 'AppBundle:Admin/ContractArtist:icon_refund.html.twig',
                    ),
                    'validate' => array(
                        'template' => 'AppBundle:Admin/ContractArtist:icon_validate.html.twig',
                    ),
                )))
        ;
    }

    public function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Infos générales')
                ->add('id')
                ->add('date', 'date', array(
                    'label' => 'Date de création',
                    'format' => 'd/m/Y',
                    'locale' => 'fr',
                    'timezone' => 'Europe/Paris',
                ))
                ->add('dateEnd', 'date', array(
                    'label' => 'Échéance',
                    'format' => 'd/m/Y',
                    'locale' => 'fr',
                    'timezone' => 'Europe/Paris',
                ))
                ->add('step', null, array(
                    'label' => 'Palier',
                    'route' => array('name' => 'show'),
                ))
                ->add('artist', null, array(
                    'label' => 'Artiste',
                    'route' => array('name' => 'show'),
                ))
                ->add('province', null, array(
                    'label' => 'Province',
                ))
                ->add('motivations', null, array(
                    'label' => 'Motivations',
                ))
                ->add('preferences.additional_info', null, array(
                    'label' => 'Infos pour les organisateurs',
                ))
                ->add('newsletter', null, array(
                    'label' => 'Newsletter associée',
                ))
            ->end()
            ->with('État')
                ->add('collected_amount', null, array(
                    'label' => 'Montant collecté',
                ))
                ->add('failed', null, array(
                    'label' => 'Échec',
                ))
                ->add('successful', null, array(
                    'label' => 'Réussi',
                ))
                ->add('cart_reminder_sent', null, array(
                    'label' => 'Rappel envoyé pour les paniers non payés qui le référencent',
                ))
                ->add('refunded', null, array(
                    'label' => 'Remboursé',
                ))
                ->add('asking_refund', null, array(
                    'label' => 'Demandes de remboursement',
                ))
                ->add('reminders_artist', null, array(
                    'label' => "Rappels envoyés à l'artiste",
                ))
                ->add('reminders_admin', null, array(
                    'label' => "Rappels envoyés aux admins",
                ))
            ->end()
            ->with('Concrétisation')
                ->add('preferences', null, array(
                    'label' => 'Préférences',
                ))
                ->add('reality', null, array(
                    'label' => 'Réalité associée'
                ))
                ->add('coartists_list', null, array(
                    'associated_property' => 'artist',
                    'label' => 'Premières parties',
                ))
            ->end()
            ->with('Soutien')
                ->add('payments', null, array(
                    'label' => 'Paiements',
                    'route' => array('name' => 'show'),
                ))
                ->add('contractsFan', null, array(
                    'label' => 'Contrats fan',
                    'route' => array('name' => 'show'),
                ))
            ->end()
        ;
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->add('dateEnd', 'date', array(
                'required' => true,
                'label' => 'Échéance',
                'html5' => false,
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'attr' => ['class' => 'datePicker'],
            ))
            ->add('motivations', null, array(
                'required' => false,
                'label' => 'Motivations du groupe',
            ))
            ->add('province', null, array(
                'required' => true,
                'label' => 'Province',
            ))
            ->end()
        ;

        $form
            ->with('Détails connus')
            ->add('reality', ConcertPossibilityType::class, array(
                'label' => false,
                'required' => false,
                'required_reality' => false,
                'is_reality' => true,
            ))
            ->end();

        $form
            ->with('Premières parties')
                ->add( 'coartists_list', 'sonata_type_collection', array(
                    'label' => false,
                    'by_reference' => false,
                ), array(
                        'edit'            => 'inline',
                        'inline'          => 'table',
                        'sortable'        => 'position',
                        'admin_code'      => ContractArtistArtistAdmin::class,
                    )
                )
            ->end()
        ;

    }
}