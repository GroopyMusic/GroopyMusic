<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 13:25
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Level;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CategoryAdmin extends BaseAdmin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('description', null, array(
                'label' => 'Description',
            ))
            ->add('formula', null, array(
                'label' => 'Formule',
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
            ->with('Données de la catégorie')
            ->add('getName', null, array(
                'label' => 'Nom de la catégorie',
            ))
            ->add('getDescription', null, array(
                'label' => 'Description de la catégorie',
            ))
            ->add('formula', null, array(
                'label' => 'Formule',
            ))
            ->add('levels', null, array(
                'label' => 'Paliers',
            ))
            ->end();
    }

    public function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Champs traductibles')
            ->add('translations', TranslationsType::class, array(
                'locales' => array('fr', 'en'),
                'fields' => [
                    'name' => [
                        'label' => 'Nom de la catégorie',
                    ],
                    'description' => [
                        'label' => 'Description de la catégorie'
                    ]
                ]
            ))
            ->end()
            ->with('Données de la catégorie')
            ->add('formula', TextType::class, array(
                'label' => 'Formule',
            ))
            ->end()
            ->with('Paliers  (La catégorie doit être créée avant d\'ajouter les paliers) ')
            ->add('levels', 'sonata_type_collection', array(
                'label' => 'Paliers',
                'by_reference' => false,
            ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                    'admin_code' => LevelAdmin::class,
                )
            )
            ->end();
    }
}