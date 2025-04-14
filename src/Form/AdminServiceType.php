<?php
namespace App\Form;

use App\Entity\Service;
use App\Entity\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class AdminServiceType extends AbstractType
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
                    ])
                ]
            ])
            ->add('serviceprix', NumberType::class, [
                'label' => 'Prix du service',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le prix du service'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix ne peut pas être vide'
                    ]),
                    new PositiveOrZero([
                        'message' => 'Le prix doit être un nombre positif ou zéro'
                    ])
                ]
            ])
            ->add('etablissement', EntityType::class, [
                'class' => Etablissement::class,
                'choice_label' => 'etabname',
                'label' => 'Établissement',
                'placeholder' => 'Choisir un établissement',
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => false // Changez de 'true' à 'false'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}