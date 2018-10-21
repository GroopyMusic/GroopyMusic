<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Artist;
use AppBundle\Entity\BaseStep;
use AppBundle\Entity\ContractArtist_Artist;
use AppBundle\Entity\Reward;
use AppBundle\Entity\StepType;
use AppBundle\Form\ConcertPossibilityType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ContractArtistAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->add('refund', $this->getRouterIdParameter() . '/refund')
            ->add('validate', $this->getRouterIdParameter() . '/validate')
            ->add('prevalidate', $this->getRouterIdParameter() . '/prevalidate')
            ->add('tickets', $this->getRouterIdParameter() . '/tickets');
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id')
            ->add('getTitle', null, array(
                'label' => 'Titre',
            ))
            ->add('date_end', 'date', array(
                'label' => 'Date de validation',
                'format' => 'd/m/Y',
            ))
            ->add('threshold', null, array(
                'label' => 'seuil',
            ))
            ->add('totalBookedTickets', null, array(
                'label' => 'Tickets bookés',
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
            ->add('test_period', null, array(
                'label' => 'Est en période de test',
            ))
            ->add('state', null, array(
                'label' => 'Etat',
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
                    'prevalidate' => array(
                        'template' => 'AppBundle:Admin/ContractArtist:icon_prevalidate.html.twig',
                    ),
                    'tickets' => array(
                        'template' => 'AppBundle:Admin/ContractArtist:icon_tickets.html.twig',
                    )
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
                ->add('motivations', null, array(
                    'label' => 'Motivations',
                ))
                ->add('promotions', null, array(
                    'label' => 'Promotions appliquées',
                ))
            ->end()
            ->with('Statistiques de vente')
                ->add('totalBookedTickets', null, array(
                    'label' => 'Tickets bookés (pondéré)',
                ))
                ->add('totalBookedTicketsRaw', null, array(
                    'label' => 'Tickets bookés (total)',
                ))
                ->add('nbCounterPartsSoldOrganic', null, array(
                    'label' => 'Dont tickets payés',
                ))
                ->add('nbCounterPartsObtainedByPromotion', null, array(
                    'label' => 'Dont tickets obtenus par promotion',
                ))
                ->add('tickets_reserved', null, array(
                    'label' => 'Dont tickets réservés',
                ))
                ->add('collected_amount', null, array(
                    'label' => 'Montant collecté',
                ))
            ->end()
            ->with('État')
                ->add('state', null, array(
                    'label' => 'Code'
                ))
                ->add('test_period', null, array(
                    'label' => 'Est en pré-validation',
                ))
                ->add('failed', null, array(
                    'label' => 'Échec',
                ))
                ->add('successful', null, array(
                    'label' => 'Réussi',
                ))
                ->add('date_success', null, array(
                    'label' => 'Date de réussite',
                ))
                ->add('refunded', null, array(
                    'label' => 'Remboursé',
                ))
                ->add('known_lineup', null, array(
                    'label' => 'Line-up connue'
                ))
            ->end()
            ->with('Autres')
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
            ->with('Tickets')
                ->add('counterParts', null, array(
                    'label' => 'Tickets',
                    'route' => array('name' => 'show'),
                ))
                ->add('getAdditionalInfo', null, array(
                    'label' => 'Informations additionnelles qui doivent figurer dans le mail avec les tickets (note sur le lieu, la bouffe, le timing, ...)',
                ))
            ->end()
            ->with('Soutien')
                ->add('artistScoresExport', null, array(
                    'label' => 'Scores des artistes',
                ))
                ->add('nbOrdersPaid', null, array(
                    'label' => 'Nombre de commandes payées',
                ))
                ->add('contractsFanExport', null, array(
                    'label' => 'Commandes payées',
                ))
                ->add('vip_inscriptions', null, array(
                    'label' => "Demandes d'accréditations",
                    'route' => array('name' => 'show'),
                ))
                ->add('volunteer_proposals', null, array(
                    'label' => "Propositions de bénévolat",
                    'route' => array('name' => 'show'),
                ))
            ->end()
            /*
            ->with('Parrainage')
                ->add('sponsorship_reward', null, array(
                    'label' => 'Récompense de parrainage',
                    'route' => array('name' => 'show'),
                    'required' => false
                ))
            ->end()
            */
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
                ->add('start_date', 'date', array(
                    'label' => 'Début des ventes',
                ))
                ->add('motivations', null, array(
                    'required' => false,
                    'label' => 'Motivations du groupe',
                ))
                ->add('tickets_reserved', null, array(
                    'required' => true,
                    'label' => 'Tickets réservés',
                ))
                ->add('known_lineup', null, array(
                    'label' => 'Line-up annoncée'
                ))
                ->add('translations', TranslationsType::class, array(
                    'required' => false,
                    'label' => 'Champs traductibles',
                    'fields' => [
                        'additional_info' => [
                            'label' => 'Informations additionnelles qui doivent figurer dans le mail avec les tickets (note sur le lieu, la bouffe, le timing, ...)',
                        ],
                    ],
                ))
            ->add('nb_closing_days', null, array(
                'label' => 'Nombre de jours sans vente avant le jour J',
            ))
            ->add('global_soldout', null, array(
                'label' => 'Sold out global',
            ))
            ->add('threshold', null, array(
                'label' => 'Seuil',
            ))
            ->add('festivaldays', null, array(
                'label' => 'Jours de festival'
            ))
            ->add('promotions', null, array(
                'label' => 'Promotions applicables'
            ))
            ->end()
        ;

       /* $form
            ->with('Parrainage')
            ->add('sponsorship_reward', EntityType::class, array(
                'class' => Reward::class,
                'choices' => $em->getRepository('AppBundle:Reward')->findNotDeletedRewards($request->getLocale()),
                'label' => 'Récompense de parrainage'
            ))
            ->end();*/


    }

    public function getExportFields()
    {
        return [
            '#' => 'id',
            'Date de création' => 'date',
            'Date de début des ventes officielles' => 'start_date',
            'Date limite pour objectif' => 'dateEnd',
            'Jours de festival' => 'festivalDaysExport',
            'Motivations' => 'motivations',
            'Amassé brut' => 'collected_amount',
            'Réussi' => 'successful',
            'Raté' => 'failed',
            'Remboursé' => 'refunded',
            'En pré-validation' => 'test_period',
            'État' => 'state',
            'Tickets bookés' => 'totalBookedTickets',
            'Dont tickets payés' => 'nbCounterPartsSoldOrganic',
            'Dont tickets offerts par promotion' => 'nbCounterPartsObtainedByPromotion',
            'Dont tickets réservés' => 'tickets_reserved',
            'Seuil' => 'min_tickets',
            'Tickets pour sold out' => 'maxTickets',
            'Tickets encore en vente' => 'crowdable',
            'Nombre de commandes validées' => 'nbOrdersPaid',
            'Commandes validées' => 'contractsFanExport',
            'Scores des artistes' => 'artistScoresExport',
            'Promotions' => 'promotionsExport',
        ];
    }
}