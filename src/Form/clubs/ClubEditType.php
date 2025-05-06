<?php

namespace App\Form\clubs;

use App\Entity\Club;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType,
    TextareaType,
    ChoiceType,
    FileType,
    DateType,
    IntegerType,
    SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clubName', TextType::class, [
                'label' => 'Nom du Club',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un nom de club']),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('clubDescription', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une description'])
                ],
                'attr' => ['rows' => 4]
            ])
            ->add('clubCategory', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Arts' => 'Arts',
                    'Astronomie' => 'Astronomie',
                    'Chimie' => 'Chimie',
                    'Ciné Club' => 'Ciné_club',
                    'Danse' => 'Danse',
                    'Débats' => 'Débats',
                    'Entrepreneuriat' => 'Entrepreneuriat',
                    'Environnement' => 'Environnement',
                    'Histoire' => 'Histoire',
                    'Jeux Vidéo' => 'Jeux_Vidéo',
                    'Langues' => 'Langues',
                    'Littérature' => 'Littérature',
                    'Mathématiques' => 'Mathématiques',
                    'Musique' => 'Musique',
                    'Photographie' => 'Photographie',
                    'Programmation' => 'Programmation',
                    'Robotique' => 'Robotique',
                    'Sciences' => 'Sciences',
                    'Sport' => 'Sport',
                    'Technologie' => 'Technologie',
                    'Théâtre' => 'Théâtre',
                    'Volontariat' => 'Volontariat',
                    'Voyage' => 'Voyage',
                ],
                'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une catégorie'])
                ]
            ])
            ->add('clubContact', TextType::class, [
                'label' => 'Contact (email ou numéro TN)',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un contact']),
                    new Assert\Regex([
                        'pattern' => '/^(?:\+216\s?\d{2}\s?\d{3}\s?\d{3}|[\w\.-]+@[\w\.-]+\.\w{2,})$/',
                        'message' => 'Entrez un email valide ou un numéro tunisien.'
                    ])
                ]
            ])
            ->add('clubLocation', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une adresse'])
                ]
            ])
            ->add('scheduleInfo', TextareaType::class, [
                'label' => 'Informations sur les horaires',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir les horaires'])
                ],
                'attr' => ['placeholder' => 'Ex : 08:00 - 17:00 du lundi au vendredi.']
            ])
            ->add('creationDate', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'disabled' => true
            ])
            ->add('membersCount', IntegerType::class, [
                'label' => 'Nombre de membres',
                'disabled' => true
            ])
            ->add('clubLogo', FileType::class, [
                'label' => 'Logo du club',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop grand ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                        'mimeTypesMessage' => 'Format d\'image invalide.'
                    ])
                ]
            ])
            ->add('bannerImage', FileType::class, [
                'label' => 'Bannière',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '4M',
                        'maxSizeMessage' => 'Le fichier est trop grand ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                        'mimeTypesMessage' => 'Format d\'image invalide.'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour le club',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Club::class,
        'csrf_protection' => true,
        'csrf_field_name' => '_token',
        'csrf_token_id'   => 'club_edit', // Add this line
    ]);
}
}