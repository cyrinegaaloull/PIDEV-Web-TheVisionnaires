<?php

namespace App\Form;

use App\Entity\Etablissement;
use App\Entity\Categorie;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotNull;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etabname', TextType::class, [
                'label' => 'Nom de l\'établissement',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'établissement ne peut pas être vide']),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de l\'établissement'
                ]
            ])
            ->add('etabaddress', TextareaType::class, [
                'label' => 'Adresse',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse ne peut pas être vide']),
                    new Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Entrez l\'adresse complète'
                ]
            ])
            ->add('horaireDateObject', DateTimeType::class, [
                'label' => 'Date et heure d\'ouverture',
                'widget' => 'single_text',
                'required' => true, // Changer false à true
                'constraints' => [
                    new NotNull(['message' => 'La date et l\'heure d\'ouverture ne peuvent pas être vides'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime())->format('Y-m-d\TH:i')
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'Région',
                'required' => true, // Changer false à true
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'La région ne peut pas être vide']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'La région ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Ile-de-France'
                ]
            ])
            ->add('geolocation', TextType::class, [
                'label' => 'Géolocalisation',
                'required' => false,
                'empty_data' => '',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/',
                        'message' => 'Le format de géolocalisation doit être valide (ex: 48.8566, 2.3522)',
                        'match' => true,
                        'normalizer' => 'trim'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 48.8566, 2.3522'
                ]
            ])
            ->add('categoryid', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'categoryname',
                'label' => 'Catégorie',
                'required' => true,
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => [
                    'class' => 'form-control select2-autosearch',
                    'data-placeholder' => 'Rechercher une catégorie...'
                ],
                'constraints' => [
                    new NotNull(['message' => 'Veuillez sélectionner une catégorie'])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['data'] && $options['data']->getEtabid() 
                    ? 'Modifier l\'établissement' 
                    : 'Ajouter l\'établissement',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
            'attr' => ['novalidate' => 'novalidate'],
            'validation_groups' => ['Default'],
            'allow_extra_fields' => true
        ]);
    }
}