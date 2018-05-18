<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 13:25
 */

namespace AppBundle\Admin;

use AppBundle\Entity\Level;
use AppBundle\Entity\Reward;
use AppBundle\Services\FormulaParserService;
use AppBundle\Services\RewardAttributionService;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Exception\ValidatorException;


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
            ->add('rewards', null, array(
                'label' => 'Récompenses'
            ))
            ->end();

    }

    public function configureFormFields(FormMapper $form)
    {
        $request = $this->getConfigurationPool()->getContainer()->get('request_stack')->getCurrentRequest();
        $rewardAttributionService = $this->getConfigurationPool()->getContainer()->get(RewardAttributionService::class);
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
                'help' => $this->constructHelpQuerryName(),
            ))
            ->end()
            ->with('Récompenses')
            ->add('rewards', EntityType::class, [
                'label' => 'Récompenses',
                'class' => Reward::class,
                'choices' => $rewardAttributionService->constructRewardSelectWithType($request->getLocale()),
                'multiple' => true,
                'required' => false
            ])
            ->end();

    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $formulaParserService = $this->getConfigurationPool()->getContainer()->get(FormulaParserService::class);
        try {
            $formulaParserService->setUserStatisticsVariables(['pr' => '10', 'me' => '5', 'am' => '4']);
            $formulaParserService->computeStatistic($object->getFormula());
        } catch (\Exception $ex) {
            return $errorElement->with('formula')->addViolation('Le format de la formule n\'est pas correct : ' . $ex->getMessage())->end();
        }
    }

    public function constructHelpQuerryName()
    {
        $formulaParserService = $this->getConfigurationPool()->getContainer()->get(FormulaParserService::class);
        $querryNames = $formulaParserService->getQuerryDescription();
        $helpMessage = "Variables : \n";
        foreach ($querryNames as $key => $name) {
            $helpMessage .= "&emsp;&emsp;" . $key . " = " . $name . "\n";
        }
        return nl2br($helpMessage);
    }
}