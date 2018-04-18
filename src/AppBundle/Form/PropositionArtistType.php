<?php

namespace AppBundle\Form;
use AppBundle\Entity\PropositionArtist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
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
                    new Length(['max' => 255, 'maxMessage' => 'proposition_artist.artistname.max'])
                ],
            ))
            ->add('demo_link', TextType::class, array(
                'label' => 'labels.proposition_artist.demo_link',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'proposition_artist.demo_link.max'])
                ],
            ))
            ->add('genres', Select2EntityType::class, [
                'required' => false,
                'label' => 'labels.proposition_artist.genres',
                'multiple' => true,
                'remote_route' => 'select2_genres',
                'class' => 'AppBundle\Entity\Genre',
                'primary_key' => 'id',
                'width' => '100%'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PropositionArtist',
        ));
    }

    public function getBlockPrefix()
    {
        return 'appbundle_proposition_artist';
    }


}