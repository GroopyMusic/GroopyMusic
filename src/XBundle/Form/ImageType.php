<?php

namespace XBundle\Form;

use XBundle\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', VichImageType::class, [
            'label' => false,
            'required' => false,
            'download_link' => false,
            'download_uri' => false,
            'image_uri' => true,
            'allow_delete' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'x_bundle_image_type';
    }
}
