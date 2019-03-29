<?php

namespace AppBundle\Form\YB;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\YBSubEvent;
use AppBundle\Form\AddressType;
use AppBundle\Form\CounterPartType;
use AppBundle\Form\PhotoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Common\Collections\ArrayCollection;
use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

class YBContractArtistType extends FormFlow
{
    protected $allowRedirectAfterSubmit = true;

    public function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'Infos générales',
                'form_type' => YBContractArtistInfosType::class,
            ),
            array(
                'label' => 'Tickets',
                'form_type' => YBContractArtistTicketsType::class,
            ),
        );
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_ybcontract_artist_type';
    }

    public function getName()
    {
        return 'createCampaign';
    }
}
