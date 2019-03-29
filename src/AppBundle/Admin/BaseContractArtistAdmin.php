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

class BaseContractArtistAdmin extends BaseAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('delete')
            ->remove('show')
            ->remove('form')
            ->remove('export')
            ->add('payments', $this->getRouterIdParameter() . '/payments');
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
                    'payments' => array(
                        'template' => 'AppBundle:Admin/ContractArtist:icon_payments.html.twig',
                    ),
                )))
        ;
    }
}