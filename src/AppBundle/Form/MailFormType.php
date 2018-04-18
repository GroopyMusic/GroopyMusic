<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/04/2018
 * Time: 12:20
 */

namespace AppBundle\Form;


use AppBundle\Entity\Artist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class MailFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', Select2EntityType::class, [
                'required' => false,
                'label' => 'Utilisateurs',
                'multiple' => true,
                'remote_route' => 'select2_users',
                'class' => 'AppBundle\Entity\User',
                'primary_key' => 'id',
                'width' => '100%',
                'attr' => [
                    'class' => 'users_select'
                ]
            ])
            ->add('all_users', CheckboxType::class, array(
                'label' => 'Envoyer à tous les utilisateurs',
                'required' => false,
                'attr' => [
                    'class' => 'all_users_checkbox'
                ]
            ))
            ->add('newsletter_users', Select2EntityType::class, [
                'required' => false,
                'label' => 'Utilisateurs inscrits à la newsletter',
                'multiple' => true,
                'remote_route' => 'select2_newsletter_users',
                'class' => 'AppBundle\Entity\User',
                'primary_key' => 'id',
                'width' => '100%',
                'attr' => [
                    'class' => 'newsletter_users_select'
                ]
            ])
            ->add('all_newsletter_users', CheckboxType::class, array(
                'label' => 'Envoyer à tous les utilisateurs inscrits à la newsletter',
                'required' => false,
                'attr' => [
                    'class' => 'all_newsletter_users_checkbox'
                ]
            ))
            ->add('artists', Select2EntityType::class, [
                'required' => false,
                'label' => 'Artistes',
                'multiple' => true,
                'remote_route' => 'select2_artists',
                'class' => 'AppBundle\Entity\Artist',
                'primary_key' => 'id',
                'width' => '100%',
                'attr' => [
                    'class' => 'artists_select'
                ]
            ])
            ->add('artist_members', ChoiceType::class, [
                'required' => false,
                'label' => "Membres",
                'multiple' => true,
                'attr' => [
                    'disabled' => 'false',
                    'class' => 'members_select'
                ]

            ])
            ->add('user_contractArtist', Select2EntityType::class, [
                'required' => false,
                'label' => 'Evénements',
                'multiple' => true,
                'remote_route' => 'select2_contractArtists',
                'class' => 'AppBundle\Entity\ContractArtist',
                'primary_key' => 'id',
                'width' => '100%',
                'attr' => [
                    'class' => 'user_contract_artist_select'
                ]
            ])
            ->add('user_participants', ChoiceType::class, [
                'required' => false,
                'label' => "Participants",
                'multiple' => true,
                'attr' => [
                    'disabled' => 'false',
                    'class' => 'user_participants_select'
                ]
            ])
            ->add('artist_contractArtist', Select2EntityType::class, [
                'required' => false,
                'label' => 'Evénements',
                'multiple' => true,
                'remote_route' => 'select2_contractArtists',
                'class' => 'AppBundle\Entity\ContractArtist',
                'primary_key' => 'id',
                'width' => '100%',
                'attr' => [
                    'class' => 'artist_contract_artist_select'
                ]
            ])
            ->add('artist_participants', ChoiceType::class, [
                'required' => false,
                'label' => "Artistes participants",
                'multiple' => true,
                'attr' => [
                    'disabled' => 'false',
                    'class' => 'artist_participants_select'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => "Email",
                'attr' => [
                    'class' => 'emails_input form-input'
                ]
            ])
            ->add('object', TextType::class, [
                'required' => true,
                'label' => "Objet",
                'attr' => [
                    'class' => 'object_mail_input form-input'
                ]

            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => "Contenu",
                'attr' => [
                    'rows' => 2,
                    'class' => 'mail_content_textarea form-input'
                ]
            ]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array());
    }
}