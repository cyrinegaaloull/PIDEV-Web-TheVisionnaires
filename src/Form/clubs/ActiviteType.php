<?php

namespace App\Form\clubs;

use App\Entity\Activite;
use App\Entity\Club;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;


class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activitename', TextType::class, [
                'label' => 'Nom de l\'activité',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un nom d\'activité']),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('activitedescription', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une description'])
                ],
            ])
            ->add('activitedate', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir la date de l\'activité'])
                ]
            ])
            ->add('starttime', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'constraints' => [                    
                    new Assert\NotBlank(['message' => 'Veuillez saisir l\'heure de début'])
                ]
            ])
            ->add('endtime', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'constraints' => [                    
                    new Assert\NotBlank(['message' => 'Veuillez saisir l\'heure de fin'])
                ]
            ])
            ->add('activitelocation', TextType::class, [
                'label' => 'Lieu',
                'constraints' => [                    
                    new Assert\NotBlank(['message' => 'Veuillez saisir une adresse'])
                ]
            ])
            ->add('activitetype', TextType::class, [
                'label' => 'Type',
                'constraints' => [                    
                    new Assert\NotBlank(['message' => 'Veuillez saisir le type d\'activité'])
                ]
            ])
            ->add('activitestatus', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À venir' => 'A_venir',
                    'En cours' => 'En_Cours',
                    'Terminé' => 'Termine',
                    'Annulé' => 'Annule',
                    'Reporté' => 'Reporte',
                ]
            ])
            ->add('activiteimage', FileType::class, [
                'label' => 'Image de l\'activité',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (jpg, png, webp)',
                    ]),
                    new Assert\NotBlank(['message' => 'Veuillez télécharger une image'])
                ],
            ])
            ->add('clubid', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'clubname',
                'label' => 'Club organisateur',
                'placeholder' => 'Sélectionner un club',
                'constraints' => [                    
                    new Assert\NotBlank(['message' => 'Veuillez choisir le club organisateur'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
            'validation_groups' => ['Default'],
        ]);
    }
}