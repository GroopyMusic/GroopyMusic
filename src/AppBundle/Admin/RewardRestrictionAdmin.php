<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 27/03/2018
 * Time: 09:55
 */

namespace AppBundle\Admin;


use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Category;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Reward;
use AppBundle\Entity\Step;
use AppBundle\Services\RewardAttributionService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
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
            ->add('query', null, array(
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
            ->add('query', null, array(
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
        $request = $this->getConfigurationPool()->getContainer()->get('request_stack')->getCurrentRequest();
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
            ->add('query', ChoiceType::class, array(
                'label' => 'Nom du query',
                'choices' => $this->constructQuerySelect(),
                'attr' => [
                    'class' => 'query_name_select'
                ],
                'choice_attr' => function ($val, $key, $index) {
                    $query_params_type = $this->getConfigurationPool()
                        ->getContainer()
                        ->get(RewardAttributionService::class)
                        ->getQuerryNamesParams();
                    switch ($query_params_type[$val]) {
                        case CounterPart::class:
                            return ['class' => 'counterPart-type'];
                        case Step::class:
                            return ['class' => 'step-type'];
                        case Artist::class:
                            return ['class' => 'artist-type'];
                        case ContractArtist::class :
                            return ['class' => 'contractArtist-type'];
                        default :
                            return ['class' => 'null-type'];
                    }
                },
            ))
            ->add('query_parameter', ChoiceType::class, array(
                'label' => 'Paramètre du query',
                'choices' => array(
                    " " => null,
                    'Artistes' => $entitiesArray['artists'],
                    'Evénements' => $entitiesArray['contractArtists'],
                    'Contre parties' => $entitiesArray['counterParts'],
                    'Paliers de salle' => $entitiesArray['steps']
                ),
                'attr' => [
                    'class' => 'query_params_select'
                ],
            ))
            ->end()
            ->with('Récompenses')
            ->add('rewards', EntityType::class, [
                'class' => Reward::class,
                'choices' => $em->getRepository('AppBundle:Reward')->findNotDeletedRewards($request->getLocale()),
                'multiple' => true,
                'required' => false
            ])
            ->end();
    }

    private function getSelectEntities()
    {
        $selectArray = [];
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $artists = $em->getRepository('AppBundle:Artist')->getArtistsForSelect();
        $contractArtists = $em->getRepository('AppBundle:ContractArtist')->getContactArtistsForSelect();
        $steps = $em->getRepository('AppBundle:Step')->getStepsForSelect();
        $counterParts = $em->getRepository('AppBundle:CounterPart')->getCounterPartsForSelect();

        foreach ($artists as $artist) {
            $selectArray['artists'][$artist->getArtistName()] = $artist->getId() . '|' . $artist->getArtistName();
        }
        foreach ($counterParts as $counterPart) {
            $selectArray['counterParts'][$counterPart->getName()] = $counterPart->getId() . '|' . $counterPart->getName();
        }
        foreach ($steps as $step) {
            $selectArray['steps'][$step->getName()] = $step->getId() . '|' . $step->getName();
        }
        foreach ($contractArtists as $contractArtist) {
            $selectArray['contractArtists'][$contractArtist->getDisplayName()] = $contractArtist->getId() . '|' . $contractArtist->getDisplayName();
        }
        ksort($selectArray['artists']);
        ksort($selectArray['counterParts']);
        ksort($selectArray['steps']);
        ksort($selectArray['contractArtists']);
        return $selectArray;
    }

    private function constructQuerySelect()
    {
        $arraySelect = [];
        $query_names = $this->getConfigurationPool()->getContainer()->get('AppBundle\Services\RewardAttributionService')->getQuerryNamesParams();
        foreach ($query_names as $name => $type) {
            $arraySelect[$name] = $name;
        }
        return $arraySelect;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $rewardAttributionService = $this->getConfigurationPool()->getContainer()->get(RewardAttributionService::class);
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $query_params = $rewardAttributionService->getQuerryNamesParams();
        $parameters = $object->getQueryParameter();
        $id = null;
        $name = null;
        if ($parameters != null) {
            $id = intval(explode('|', $parameters)[0]);
            $name = explode('|', $parameters)[1];
        }
        try {
            switch ($query_params[$object->getQuery()]) {
                case ContractArtist::class :
                    if ($parameters == null) {
                        $errorElement
                            ->with('query_parameter')
                            ->addViolation('Aucun paramètre trouvé. Attendu -> ContractArtist')
                            ->end();
                    } else {
                        $entity = $em->getRepository('AppBundle:ContractArtist')->find($id);
                        if ($entity != null && $entity->getDisplayName() != $name) {
                            $errorElement
                                ->with('query_parameter')
                                ->addViolation('Type du paramètre incorrecte. Attendu -> ContractArtist')
                                ->end();
                        }
                    }
                    break;
                case CounterPart::class :
                    if ($parameters == null) {
                        $errorElement
                            ->with('query_parameter')
                            ->addViolation('Aucun paramètre trouvé. Attendu -> CounterPart')
                            ->end();
                    } else {
                        $entity = $em->getRepository('AppBundle:CounterPart')->find($id);
                        if ($entity != null && $entity->getName() != $name) {
                            $errorElement
                                ->with('query_parameter')
                                ->addViolation('Type du paramètre incorrecte. Attendu -> CounterPart')
                                ->end();
                        }
                    }
                    break;
                case Step::class :
                    if ($parameters == null) {
                        $errorElement
                            ->with('query_parameter')
                            ->addViolation('Aucun paramètre trouvé. Attendu -> Step')
                            ->end();
                    } else {
                        $entity = $em->getRepository('AppBundle:Step')->find($id);
                        if ($entity != null && $entity->getName() != $name) {
                            $errorElement
                                ->with('query_parameter')
                                ->addViolation('Type du paramètre incorrecte. Attendu -> Step')
                                ->end();
                        }
                    }
                    break;
                case Artist::class:
                    if ($parameters == null) {
                        $errorElement
                            ->with('query_parameter')
                            ->addViolation('Aucun paramètre trouvé. Attendu -> Artist')
                            ->end();
                    } else {
                        $entity = $em->getRepository('AppBundle:Artist')->find($id);
                        if ($entity != null && $entity->getArtistName() != $name) {
                            $errorElement
                                ->with('query_parameter')
                                ->addViolation('Type du paramètre incorrecte. Attendu -> Artist')
                                ->end();
                        }
                    }
                    break;
                default :
                    if ($parameters != null) {
                        $errorElement
                            ->with('query_parameter')
                            ->addViolation('Aucun paremètre n\'est requis pour ce query')
                            ->end();
                    }
                    break;
            }
        } catch (\Throwable $th) {
            $errorElement
                ->with('query_parameter')
                ->addViolation('Une erreur est survenue lors de la validation du paramètre : ' . $th->getMessage())
                ->end();
        }
    }
}