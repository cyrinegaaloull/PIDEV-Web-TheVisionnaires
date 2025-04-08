<?php

namespace App\Form\clubs;

use App\Entity\Activite;
use App\Entity\Club;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ActiviteEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activitename', null, [
                'label' => 'Nom de l’activité',
                'disabled' => true,                
            ])
            ->add('activitedescription', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4],
                'disabled' => true,
            ])
            ->add('activitedate', null, [
                'label' => 'Date de l’activité',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir la date de l\'activité'])
                ]
            ])
            ->add('activitelocation', null, [
                'label' => 'Lieu',
                'disabled' => true,                
            ])
            ->add('activitetype', null, [
                'label' => 'Type d’activité',
                'disabled' => true,                
            ])
            ->add('starttime', null, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'disabled' => true,                
            ])
            ->add('endtime', null, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'disabled' => true,                
            ])
            ->add('activitestatus', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À venir' => 'A_venir',
                    'En cours' => 'En_Cours',
                    'Terminé' => 'Termine',
                    'Reporté' => 'Reporte',
                    'Annulé' => 'Annule',
                ],
            ])
            ->add('clubid', EntityType::class, [
                'class' => Club::class,
                'choice_label' => 'clubname',
                'label' => 'Club associé',
                'disabled' => true,
            ])
            ->add('activiteimage', null, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier est trop grand ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                        'mimeTypesMessage' => 'Format d\'image invalide.'
                    ])
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
