<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 09/03/2018
 * Time: 14:41
 */

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;


class PropositionContractArtistAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('edit');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('contactPerson.displayName', null, array(
                'label' => 'Personne de contact',
            ))
            ->add('contactPerson.mail', null, array(
                'label' => 'Email de contact',
            ))
            ->add('payable', null, array(
                'label' => 'Payant',
            ))
            ->add('period_start_date', null, array(
                'label' => 'Date de début/unique',
            ))
            ->add('period_end_date', null, array(
                'label' => 'Date de fin'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                )
            ));
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->with('Personne de contact')
                ->add('contactPerson.lastname', null, array(
                    'label' => 'Nom',
                ))
                ->add('contactPerson.firstname', null, array(
                    'label' => 'Prénom',
                ))
                ->add('contactPerson.mail', null, array(
                    'label' => 'Adresse email',
                ))
                ->add('contactPerson.phone', null, array(
                    'label' => 'Numero de téléphone',
                ))
            ->end()
            ->with("Artiste")
                ->add('Artist', null, array(
                    'label' => 'Artiste existant',
                ))
                ->add('propositionArtist.artistname', null, array(
                    'label' => 'Nom du nouvel artiste',
                ))
                ->add('propositionArtist.demo_link', 'url', array(
                    'label' => 'Extrait musical du nouvel artiste',
                    'attributes' => ['target' => '_blank']
                ))
                ->add('propositionArtist.genres', null, array(
                    'label' => 'Genre(s) du nouvel artiste',
                ))
            ->end()
            ->with("Emplacement")
                ->add('province', null, array(
                    'label' => 'Province souhaitée',
                ))
                ->add('propositionHall.name', null, array(
                    'label' => 'Nom de la salle proposée',
                ))
                ->add('propositionHall.contact_email', null, array(
                    'label' => 'Email de la salle proposée',
                ))
                ->add('propositionHall.contact_phone', null, array(
                    'label' => 'Téléphone de la salle proposée',
                ))
                ->add('propositionHall.province', null, array(
                    'label' => 'Province de la salle proposée',
                ))
            ->end()
            ->with('Information générale')
                ->add('reason', null, array(
                    'label' => 'Raison de l\'evnement',
                ))
                ->add('nb_expected', null, array(
                    'label' => 'Nombre de personnes attendues',
                ))
                ->add('payable', null, array(
                    'label' => 'Payant',
                ))
                ->add('period_start_date', null, array(
                    'label' => 'Date de début ou date unique',
                ))
                ->add('period_end_date', null, array(
                    'label' => 'Date de fin',
                ))
                ->add('day_commentary', null, array(
                    'label' => 'Commentaire sur le jour de la semaine',
                ))
                ->add('commentary', null, array(
                    'label' => 'Commentaire',
                ))
            ->end();
        ;
    }
}