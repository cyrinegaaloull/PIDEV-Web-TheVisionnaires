<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Length;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('servicename', TextType::class, [
                'label' => 'Nom du service',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du service'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du service ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('serviceprix', NumberType::class, [
                'label' => 'Prix du service',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prix du service',
                    'step' => '0.01'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix ne peut pas être vide'
                    ]),
                    new PositiveOrZero([
                        'message' => 'Le prix doit être un nombre positif ou zéro'
                    ])
                ]
            ]);
            
        // Si l'établissement est en lecture seule, on le cache ou on le désactive
        if ($options['etablissement_readonly']) {
            $builder->add('etablissement', EntityType::class, [
                'class' => Etablissement::class,
                'choice_label' => 'etabname',
                'label' => 'Établissement',
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control disabled'
                ]
            ]);
        } else {
            $builder->add('etablissement', EntityType::class, [
                'class' => Etablissement::class,
                'choice_label' => 'etabname',
                'label' => 'Établissement',
                'placeholder' => 'Choisir un établissement',
                'attr' => [
                    'class' => 'form-control select2-autocomplete'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un établissement'
                    ])
                ]
            ]);
        }
        
        $builder->add('submit', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => ['class' => 'btn btn-primary mt-3']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'etablissement_readonly' => false
        ]);
    }
}