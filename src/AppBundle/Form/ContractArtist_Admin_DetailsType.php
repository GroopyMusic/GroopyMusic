<?php

namespace AppBundle\Form;

use AppBundle\Entity\StepType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ContractArtist_Admin_DetailsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $contract = $builder->getData();
        $step = $contract->getStep();

        if ($step != null && $step->getType()->getName() == StepType::TYPE_CONCERT) {
            $builder->add('reality', ConcertPossibilityType::class, array(
                'required' => false,
                'step' => $step,
            ));
        }

        $builder->add('coartists_list', ContractArtistArtistType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_admin_contractartist_details';
    }
}