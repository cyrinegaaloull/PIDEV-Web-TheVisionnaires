<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Title Field
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter a title',
                    'minlength' => 3, // HTML5 validation hint
                    'maxlength' => 255,
                ],
                'required' => true, // Ensures the field is not empty
            ])

            // Content Field
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => [
                    'placeholder' => 'Enter your content here...',
                    'minlength' => 10, // HTML5 validation hint
                    'rows' => 6, // Adjust textarea height
                ],
                'required' => true,
            ])

            // Category Field
            ->add('category', TextType::class, [
                'label' => 'Category',
                'attr' => [
                    'placeholder' => 'Enter a category',
                    'maxlength' => 255,
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class, // Links the form to the Post entity
        ]);
    }
}