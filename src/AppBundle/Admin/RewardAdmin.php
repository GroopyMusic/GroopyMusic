<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 27/03/2018
 * Time: 10:19
 */

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\ReductionReward;
use AppBundle\Entity\RewardRestriction;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Choice;

class RewardAdmin extends BaseAdmin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $subject = $this->getSubject();
        $listMapper
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('type', null, array(
                'label' => 'Type',
            ))
            ->add('getDispayDeleted', null, array(
                'label' => 'Supprimé',
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
        $subject = $this->getSubject();
        $showMapper
            ->with('Données récompenses communes')
            ->add('name', null, array(
                'label' => 'Nom',
            ))
            ->add('feature', null, array(
                'label' => 'Caractéristique',
            ))
            ->add('max_use', null, array(
                'label' => 'Nombre d\'utilisation',
            ))
            ->add('validity_period', null, array(
                'label' => 'Nombre de jours valides',
            ))
            ->end()
            ->with('Restrictions')
            ->add('restrictions', null, array(
                'label' => 'Restrictions',
            ))
            ->end();
        if ($subject instanceof InvitationReward) {
            $showMapper
                ->with('Récompense invitation')
                ->add('start_date', null, array(
                    'label' => 'Date de début',
                    'required' => false
                ))
                ->add('end_date', null, array(
                    'label' => 'Date de fin',
                    'required' => false
                ))
                ->end();
        } elseif ($subject instanceof ConsomableReward) {
            $showMapper
                ->with('Récompense consommation')
                ->add('quantity', null, array(
                    'label' => 'Nombre de tickets',
                ))
                ->add('type_consomable', null, array(
                    'label' => 'Type de consommation',
                ))
                ->add('value', null, array(
                    'label' => 'Valeur d\'un ticket',
                ))
                ->end();
        } elseif ($subject instanceof ReductionReward) {
            $showMapper
                ->with('Récompense réduction')
                ->add('reduction', null, array(
                    'label' => 'Pourcentage de réduction',
                ))
                ->end();
        }
    }

    public function configureFormFields(FormMapper $form)
    {
        $subject = $this->getSubject();
        $form
            ->with('Champs traductibles')
            ->add('translations', TranslationsType::class, array(
                'locales' => array('fr', 'en'),
                'fields' => [
                    'name' => [
                        'label' => 'Nom de la récompense',
                    ],
                    'feature' => [
                        'label' => 'Caractéristique de la récompense'
                    ]
                ]
            ))
            ->end()
            ->with('Données récompenses communes')
            ->add('max_use', IntegerType::class, array(
                'label' => 'Nombre d\'utilisation',
            ))
            ->add('validity_period', IntegerType::class, array(
                'label' => 'Nombres de jours valides',
            ))
            ->end();
        if ($subject instanceof InvitationReward) {
            $form
                ->with('Récompense invitation')
                ->add('start_date', DateTimeType::class, array(
                    'label' => 'Date de début',
                    'required' => false
                ))
                ->add('end_date', DateTimeType::class, array(
                    'label' => 'Date de fin',
                    'required' => false
                ))
                ->end();
        } elseif ($subject instanceof ConsomableReward) {
            $form
                ->with('Récompense consommation')
                ->add('quantity', IntegerType::class, array(
                    'label' => 'Nombre de tickets',
                ))
                ->add('type_consomable', ChoiceType::class, array(
                    'label' => 'Type de consommation',
                    'choices' => array(
                        "Boisson" => "Boisson",
                        "Nourriture" => "Nourriture"
                    )
                ))
                ->add('value', IntegerType::class, array(
                    'label' => 'Valeur d\'un ticket',
                ))
                ->end();
        } elseif ($subject instanceof ReductionReward) {
            $form
                ->with('Récompense réduction')
                ->add('reduction', IntegerType::class, array(
                    'label' => 'Pourcentage de réduction',
                ))
                ->end();
        }
        $form
            ->with('Réstrictions')
            ->add('restrictions', EntityType::class, [
                'class' => RewardRestriction::class,
                'multiple' => true,
                'required' => false
            ])
            ->end();

    }
}