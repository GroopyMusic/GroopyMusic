<?php

namespace XBundle\Form;

use AppBundle\Entity\Artist;
use AppBundle\Repository\ArtistRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use XBundle\Entity\Project;
use XBundle\Entity\Tag;
use XBundle\Form\ImageType;

class ProjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre du projet',
                'required' => true
            ))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $project = $event->getData();
                $event->getForm()->add('artist', EntityType::class, array(
                    'class' => Artist::class,
                    'choice_label' => 'artistname',
                    'query_builder' => function (ArtistRepository $ar) use ($project) {
                        return $ar->baseQueryBuilder()
                            ->innerJoin('a.artists_user', 'au')
                            ->where('au.user = :user')
                            ->setParameter('user', $project->getUser())
                            ->andWhere('a.deleted = 0');
                    },
                    'label' => 'Artiste associé',
                    'required' => true
                ));
            })
            ->add('description', TextareaType::class, array(
                'label' => 'Description',
                'required' => true
            ))
            ->add('dateEnd', DateTimeType::class, array(
                'label' => 'Date de clôture du financement participatif',
                'required' => true
            ))
            ->add('threshold', IntegerType::class, array(
                'label' => 'Seuil de validation (optionnel)',
                'required' => false
            ))
            ->add('tag', EntityType::class, array(
                'label' => 'Catégorie',
                'class' => Tag::class,
                'choice_label' => 'name',
                'required' => true
            ))
            ->add('coverpic', ImageType::class, array(
                'label' => 'Photo de couverture',
                'required' => false
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Enregistrer'
            ))
        ;

        /*if ($option['creation']) {
            $builder
                ->add('noThreshold', CheckboxType::class, array(
                    'label' => 'Pas de seuil de validation',
                    'required' => false
                ))
            ;
        }*/
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Project::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'xbundle_project';
    }


}
