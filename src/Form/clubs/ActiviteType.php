<?php

namespace App\Form\clubs;

use App\Entity\Activite;
use App\Entity\Club;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\{
    TextType, TextareaType, FileType, DateType, TimeType, ChoiceType, SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('activitename', TextType::class, [
                'label' => 'Nom de l’activité',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un nom.']),
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('activitedescription', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une description.']),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La description doit comporter au moins {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('activiteimage', FileType::class, [
                'label' => 'Image de l’activité',
                'mapped' => false,
                'required' => !$isEdit,
                'constraints' => array_filter([
                    !$isEdit ? new Assert\NotBlank(['message' => 'L’image est requise.']) : null,
                    new Assert\Image([
                        'maxSize' => '3M',
                        'maxSizeMessage' => 'L’image est trop grande ({{ size }} {{ suffix }}). Max {{ limit }}.',
                        'mimeTypesMessage' => 'Format d’image invalide.'
                    ])
                ])
            ])
            ->add('activitedate', DateType::class, [
                'label' => 'Date de l’activité',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir une date.']),
                    new Assert\GreaterThan([
                        'value' => 'today',
                        'message' => 'La date doit être ultérieure à aujourd’hui.'
                    ])
                ]
            ])
            ->add('activitelocation', TextType::class, [
                'label' => 'Lieu',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir un lieu.'])
                ]
            ])
            ->add('starttime', TimeType::class, [
                'label' => 'Heure de début',
                'input' => 'datetime',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une heure de début.'])
                ]
            ])
            ->add('endtime', TimeType::class, [
                'label' => 'Heure de fin',
                'input' => 'datetime',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir une heure de fin.'])
                    // Rule end < start will be handled manually in controller or using a custom validator
                ]
            ])
            ->add('activitetype', TextType::class, [
                'label' => 'Type',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez spécifier un type.'])
                ]
            ])
            ->add('activitestatus', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À venir' => 'A_venir',
                    'En cours' => 'En_cours',
                    'Terminé' => 'Termine',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un statut.'])
                ]
            ])
            ->add('clubid', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'clubname',
                'label' => 'Club organisateur',
                'placeholder' => 'Sélectionnez un club',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un club.'])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isEdit ? 'Mettre à jour l’activité' : 'Enregistrer l’activité',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
            'is_edit' => false,
        ]);
    }
}
