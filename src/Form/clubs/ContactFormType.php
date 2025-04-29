<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre Email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email.']),
                    new Email(['message' => 'Veuillez entrer un email valide.']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre Message',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un message.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
