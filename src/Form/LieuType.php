<?php

namespace App\Form;

use App\Entity\Lieu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Valid;


class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('lieuname', TextType::class, [
            'label' => 'Nom du lieu',
            'attr' => [
                'class' => 'form-control',
                'readonly' => true, // 👈 instead of disabled
            ],
            'mapped' => true,     // must be true to update entity
        ])
        
        
            ->add('lieuaddress', TextType::class)
            ->add('lieudescription', TextareaType::class)
            ->add('lieucategory', ChoiceType::class, [
                'choices' => [
                    'Restaurant' => 'RESTAURANT',
                    'Parc' => 'PARK',
                    'Musée' => 'MUSEE',
                    'Hôtel' => 'HOTEL',
                    'Centre commercial' => 'CENTRE_SHOPPING',
                    'Cinéma' => 'CINEMA',
                    'Librairie' => 'LIBRAIRIE',
                    'Stade' => 'STADE',
                    'Plage' => 'PLAGE',
                ],
                'placeholder' => 'Choisissez une catégorie',
            ])
            ->add('lieuopeninghours', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'string', // Saves as string like "14:00"
            ])
            ->add('lieuclosinghours', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'string',
            ])
            ->add('lieunumber', IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 10000000, 'max' => 99999999],
            ])         
            ->add('lieuimage', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image du lieu',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, WEBP)',
                    ])
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
            'disabled_name' => false,
            'constraints' => [new Valid()], // 👈 this is the fix
        ]);
    }
    
    
}
