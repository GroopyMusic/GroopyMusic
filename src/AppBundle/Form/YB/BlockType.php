<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\Block;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BlockType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'label' => 'Nom du bloc'
            ))
            ->add('type', TextType::class, array(
                'required' => true,
                'label' => 'Type du bloc'
            ))
            ->add('capacity', IntegerType::class, array(
                'required' => true,
                'label' => 'Capacité totale du bloc',
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                ],
            ))
            ->add('freeSeating', CheckboxType::class, array(
                'required' => false,
                'label' => 'Le placement est libre dans ce bloc'
            ))
        ;
    }

    public function validate(Block $block, ExecutionContextInterface $context){
        if ($block->getCapacity() === 0){
            $context->addViolation("La capacité du bloc doit être supérieure à 0.");
        }
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => Block::class,
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_block_type';
    }


}