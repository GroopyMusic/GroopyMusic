<?php

namespace AppBundle\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class PropositionArtistType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artistname', TextType::class, array(
                'label' => 'labels.proposition_artist.artistname',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 67, 'maxMessage' => 'Le nom de l\'artiste ou du groupe ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('demo_link', TextType::class, array(
                'label' => 'labels.proposition_artist.demo_link',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'Le lien ne peut excéder {{ limit }} caractères.'])
                ],
            ))
            ->add('genres', Select2EntityType::class, [
                'required' => false,
                'label' => 'labels.proposition_artist.genres',
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => 'AppBundle\Entity\Genre',
                'primary_key' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionArtist'
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_artist';
    }


}