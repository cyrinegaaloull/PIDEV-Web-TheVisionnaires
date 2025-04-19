<?php

namespace App\Form;

use App\Entity\Etablissement;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminEtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etabname', TextType::class, [
                'label' => 'Nom de l\'établissement',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de l\'établissement'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de l\'établissement ne peut pas être vide'
                    ])
                ]
            ])
            ->add('etabaddress', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'adresse complète'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse ne peut pas être vide'
                    ])
                ]
            ])
            ->add('horaireDateObject', DateTimeType::class, [
                'label' => 'Horaire d\'ouverture',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'Région',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la région'
                ]
            ])
            ->add('geolocation', TextType::class, [
                'label' => 'Coordonnées GPS (latitude, longitude)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 36.8065, 10.1815'
                ],
                'required' => false
            ])
            ->add('categoryid', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'categoryname',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une catégorie'
                    ])
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de l\'établissement',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
        ]);
    }
}