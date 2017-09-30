<?php

namespace AppBundle\Form;

use AppBundle\Entity\ContractArtist;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ContractArtistFlow extends FormFlow
{
    private $user;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'Choix de la salle',
                'form_type' => ContractArtistType::class,
            ),
            array(
                'label' => 'Choix de la date',
                'form_type' => ContractArtistType::class,
            ),
            array(
                'label' => 'Confirmation',
                'form_type' => ContractArtistType::class,
            )
        );
    }

    public function getFormOptions($step, array $options = array())
    {
        $options = parent::getFormOptions($step, $options);

        /** @var ContractArtist $formData */
        $formData = $this->getFormData();

        if($step == 1) {
            $options['user'] = $this->user;
        }

        elseif($step == 2) {
            $step = $formData->getStep();
            $province = $formData->getProvince();
            $available_dates = $step->getAvailableDatesFormatted($province);
            if(count($available_dates) == 0) {
                $available_dates = $step->getAvailableDatesFormatted();
            }
            $options['available-dates'] = $available_dates;
        }

        return $options;
    }

    public function getName()
    {
        return 'createcontract';
    }
}