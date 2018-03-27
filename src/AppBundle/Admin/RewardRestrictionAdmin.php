<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 27/03/2018
 * Time: 09:55
 */

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Reward;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RewardRestrictionAdmin extends BaseAdmin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, array(
                'label' => 'Nom'
            ))
            ->add('querry_name', null, array(
                'label' => 'Nom du querry'
            ))
            ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                )
            );

    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, array(
                'label' => 'Nom'
            ))
            ->add('description', null, array(
                'label' => 'Description'
            ))
            ->add('querry_name', null, array(
                'label' => 'Nom du querry'
            ))
            ->add('rewards',null,array(
                'label' => 'Récompenses'
            ));

    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Champs traductibles')
            ->add('translations', TranslationsType::class, array(
                'locales' => array('fr', 'en'),
                'fields' => [
                    'name' => [
                        'label' => 'Nom de la réstrictions',
                    ],
                    'description' => [
                        'label' => 'Description de la réstrictions'
                    ]
                ]
            ))
            ->end()
            ->with('Données de la réstrictions')
            ->add('querry_name', TextType::class, array(
                'label' => 'Nom du querry',
            ))
            ->end()
            ->with('Récompenses')
            ->add('rewards', EntityType::class, [
                'class' => Reward::class,
                'multiple' => true,
                'required' => false
            ])
            ->end();
    }
}