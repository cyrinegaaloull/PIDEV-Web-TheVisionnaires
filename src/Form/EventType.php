<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventname', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('eventdescription', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4, 'class' => 'form-control'],
            ])
            ->add('eventcategory', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Concert' => 'CONCERT',
                    'Exposition' => 'EXPOSITION',
                    'Conférence' => 'CONFERENCE',
                    'Festival' => 'FESTIVAL',
                    'Sport' => 'SPORT',
                    'Théâtre' => 'THEATRE',
                ],
                'placeholder' => 'Choisir une catégorie',
            ])
            ->add('eventdate', DateType::class, [
                'label' => 'Date de l\'événement',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ticketprice', IntegerType::class, [
                'label' => 'Prix du billet (DT)',
                'attr' => ['min' => 0]
            ])
            ->add('eventimage', FileType::class, [
                'label' => 'Image (jpg/png/webp)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP.',
                    ])
                ],
            ])
            ->add('maxtickets', IntegerType::class, [
                'label' => 'Nombre de billets',
                'required' => true,
                'attr' => ['min' => 1],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
