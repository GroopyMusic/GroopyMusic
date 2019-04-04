<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\BlockRow;
use AppBundle\Entity\YB\Seat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BlockType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){

        if (!$options['row']) {
            $builder
                ->add('name', TextType::class, array(
                    'required' => true,
                    'label' => 'Nom du bloc'
                ))
                ->add('type', ChoiceType::class, array(
                    'required' => true,
                    'label' => 'Type du bloc',
                    'choices' => [
                        'Assis (hors balcon)' => 'Assis',
                        'Assis (sur balcon)' => 'Balcon',
                        'Debout' => 'Debout',
                    ],
                    'attr' => ['class' => 'block-type'],
                ))
                ->add('capacity', IntegerType::class, array(
                    'required' => true,
                    'label' => 'Capacité totale du bloc',
                    'constraints' => [
                        new Assert\GreaterThanOrEqual(['value' => 0]),
                    ],
                ))
                ->add('notSquared', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Mon bloc n\'est pas carré/rectangulaire',
                ))
                ->add('nbRows', IntegerType::class, array(
                    'required' => false,
                    'label' => 'Nombre de rangées',
                ))
                ->add('rowLabel', ChoiceType::class, array(
                    'required' => false,
                    'label' => 'Type de numérotation',
                    'choices' => [
                        'A,B,C,D,...' => 1,
                        '1,2,3,4,...' => 2,
                    ],
                    'required' => true,
                ))
                ->add('nbSeatsPerRow', IntegerType::class, array(
                    'required' => false,
                    'label' => 'Nombre de sièges par rangée',
                ))
                ->add('seatLabel', ChoiceType::class, array(
                    'required' => false,
                    'label' => 'Type de numérotation',
                    'choices' => [
                        '1,2,3,4,...' => 2,
                        'A,B,C,D,...' => 1,

                    ],
                    'required' => true,
                ))
                ->add('freeSeating', CheckboxType::class, array(
                    'required' => false,
                    'label' => 'Le placement est libre dans ce bloc',
                ));
        } else {
            $builder
                ->add('submit', SubmitType::class, array(
                    'label' => 'Enregistrer',
                ))
                ->add('rows', CollectionType::class, array(
                    'label' => 'Ajout d\'une rangée',
                    'entry_type' => BlockRowType::class,
                    'entry_options' => array(
                        'label' => false,
                    ),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                    'attr' => ['class' => 'third-collection'],
                ));
        }
    }

    public function validate(Block $block, ExecutionContextInterface $context){
        if ($block->getCapacity() === 0){
            $context->addViolation("La capacité du bloc doit être supérieure à 0.");
        }
        if ($block->getType() === 'Debout'){
            $block->constructAllUp();
        } else {
            if ($block->getFreeSeating()){
                $block->constructFreeSeating();
            } else {
                $computedCapacity = $block->getNbSeatsOfBlock();//$block->getNbRows() * $block->getNbSeatsPerRow();
                if ($block->getCapacity() !== $computedCapacity){
                    $context->addViolation('La capacité globale et le nombre de siège ne correspondent pas ! '.$block->getCapacity().' != '.$computedCapacity);
                } else if ($block->getNbRows() > 26 && $block->getRowLabel() === 1){
                    $context->addViolation('Il n\'y a que 26 lettres dans l\'alphabet, donc maximum 26 rangées possible!');
                } else if ($block->getNbSeatsPerRow() > 26 && $block->getSeatLabel() === 1){
                    $context->addViolation('Il n\'y a que 26 lettres dans l\'alphabet, donc maximum 26 sièges par rangée possible!');
                } else {
                    if ($block->getNbRows() === null || $block->getNbSeatsPerRow() === null){
                        $context->addViolation("Vous devez renseigner des rangées et des sièges !");
                    }
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => Block::class,
            'constraints' => array(
                new Assert\Callback(array($this, 'validate'))
            ),
            'row' => false,
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_block_type';
    }


}