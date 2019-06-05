<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\CustomTicket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomTicketType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('imageAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Rajouter une image',
            ))
            ->add('venueMapAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Afficher le plan de la salle',
            ))
            ->add('commuteAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Afficher, sur une carte, les gares de trains/bus les plus proche de votre salle',
            ))
            ->add('commuteSNCBAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Gares SNCB (trains)',
            ))
            ->add('commuteSTIBAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Arrêts STIB (tram, métros et bus bruxellois)',
            ))
            ->add('commuteTECAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Arrêts TEC (tram et bus wallons)',
            ))
            ->add('publicTransportTextInfosAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Décrire comment se rendre à votre salle',
            ))
            ->add('publicTransportTextInfos', TextareaType::class, array(
                'required' => false,
                'label' => 'Taper ici votre texte'
            ))
            ->add('customInfosAdded', CheckboxType::class, array(
                'required' => false,
                'label' => 'Afficher un texte personnalisé',
            ))
            ->add('customInfos', TextareaType::class, array(
                'required' => false,
                'label' => 'Taper ici votre texte'
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer',
                'attr' => array('class' => 'btn btn-success'),
            ));
        ;
    }

    public function validate(CustomTicket $customTicket, ExecutionContextInterface $context){
        if ($customTicket->isCommuteAdded()){
            if (!$customTicket->isCommuteSNCBAdded() && !$customTicket->isCommuteSTIBAdded() && !$customTicket->isCommuteTECAdded()){
                $context->addViolation("Vous avez décidé d'afficher les transports en communs sur une carte, vous devez sélectionner au minimum un type de transport !");
            }
        }
        if (!$customTicket->isCommuteAdded()){
            $customTicket->setCommuteSNCBAdded(false);
            $customTicket->setCommuteSTIBAdded(false);
            $customTicket->setCommuteTECAdded(false);
        }
        if ($customTicket->isPublicTransportTextInfosAdded() && $customTicket->getPublicTransportTextInfos() === ''){
            $customTicket->setPublicTransportTextInfosAdded(false);
        }
        if ($customTicket->isCustomInfosAdded() && $customTicket->getCustomInfos() === ''){
            $customTicket->setCustomInfosAdded(false);
        }
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => CustomTicket::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_custom_ticket_type';
    }

}