<?php

namespace AppBundle\Form\YB;

use AppBundle\Entity\YB\BlockRow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockRowType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Libellé',
                'required' => true,
            ))
            ->add('nbSeats', IntegerType::class, array(
                'label' => 'Nombre de siège',
                'required' => true,
            ))
            ->add('numerotationSystem', ChoiceType::class, array(
                'label' => 'Numérotation',
                'required' => true,
                'choices' => [
                    '1,2,3,4,...' => 2,
                    'A,B,C,D,...' => 1,
                ],
            ));
    }

    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults([
            'data_class' => BlockRow::class,
        ]);
    }

    public function getBlockPrefix(){
        return 'app_bundle_block_row_type';
    }
}