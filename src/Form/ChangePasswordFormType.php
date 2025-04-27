<?php
// src/Form/ChangePasswordFormType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => [
                    'label' => 'New Password',
                    'attr'  => [
                        'autocomplete' => 'new-password',
                        'class'        => 'form-control mb-3',
                        'placeholder'  => 'Enter your new password',
                    ],
                ],
                'second_options'  => [
                    'label' => 'Confirm New Password',
                    'attr'  => [
                        'autocomplete' => 'new-password',
                        'class'        => 'form-control mb-3',
                        'placeholder'  => 'Confirm your new password',
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                'mapped'          => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // No data_class: handle hashing and setting manually in controller
        $resolver->setDefaults([]);
    }
}
