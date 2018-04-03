<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 27/03/2018
 * Time: 09:55
 */

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Category;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Reward;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RewardRestrictionAdmin extends BaseAdmin
{

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, array(
                'label' => 'Nom'
            ))
            ->add('querry', null, array(
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
            ->add('querry', null, array(
                'label' => 'Nom du querry'
            ))
            ->add('rewards', null, array(
                'label' => 'Récompenses'
            ));

    }

    public function configureFormFields(FormMapper $form)
    {
        $entitiesArray = $this->getSelectEntities();
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
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
            ->add('querry', ChoiceType::class, array(
                'label' => 'Nom du querry',
                'choices' => $this->constructQuerrySelect()
            ))
            ->add('querry_parameter', ChoiceType::class, array(
                'label' => 'Paramètre du querry',
                'choices' => array(
                    " " => null,
                    'Artistes' => $entitiesArray['artists'],
                    'Concert' => $entitiesArray['contractArtists'],
                    'Vente' => $entitiesArray['counterParts'],
                    'Palier' => $entitiesArray['steps']
                ),
            ))
            ->end()
            ->with('Récompenses')
            ->add('rewards', EntityType::class, [
                'class' => Reward::class,
                'choices' => $em->getRepository('AppBundle:Reward')->findNotDeletedRewards(),
                'multiple' => true,
                'required' => false
            ])
            ->end();
    }

    private function getSelectEntities()
    {
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $artists = $em->getRepository('AppBundle:Artist')->getArtistsForSelect();
        $contractArtists = $em->getRepository('AppBundle:ContractArtist')->getContactArtistsForSelect();
        $steps = $em->getRepository('AppBundle:Step')->getStepsForSelect();
        $counterParts = $em->getRepository('AppBundle:CounterPart')->getCounterPartsForSelect();
        return $this->constructSelect($artists, $contractArtists, $steps, $counterParts);
    }

    private function constructSelect($artists, $contractArtists, $steps, $counterParts)
    {
        $selectArray = [];
        foreach ($artists as $artist) {
            $selectArray['artists'][$artist->getArtistName()] = $artist->getId();
        }
        foreach ($counterParts as $counterPart) {
            $selectArray['counterParts'][$counterPart->getName()] = $counterPart->getId();
        }
        foreach ($steps as $step) {
            $selectArray['steps'][$step->getName()] = $step->getId();
        }
        foreach ($contractArtists as $contractArtist) {
            $selectArray['contractArtists'][$contractArtist->getDisplayName()] = $contractArtist->getId();
        }
        return $selectArray;
    }

    private function constructQuerrySelect()
    {
        $arraySelect = [];
        $querry_names = $this->getConfigurationPool()->getContainer()->get('AppBundle\Services\RewardAttributionService')->getQuerryNames();
        foreach ($querry_names as $querry) {
            $arraySelect[$querry] = $querry;
        }
        return $arraySelect;
    }
}