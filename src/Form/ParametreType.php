<?php

namespace App\Form;

use App\Entity\Parametre;
use App\Entity\Periode;
use App\Entity\Preponderance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParametreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'domicile', type: EntityType::class, options: [
                'class' => Preponderance::class,                
                'choice_label' => 'libelle',
                'mapped' => false,
                'label' => 'Domicile',
                'required' => false,
                'placeholder' => '--- Prépondérance ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'exterieur', type: EntityType::class, options: [
                'class' => Preponderance::class,                
                'choice_label' => 'libelle',
                'mapped' => false,
                'label' => 'Extérieur',
                'required' => false,
                'placeholder' => '--- Prépondérance ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'premiereMT', type: EntityType::class, options: [
                'class' => Periode::class,                
                'choice_label' => 'libelle',
                'mapped' => false,
                'label' => 'Première mi-temps',
                'required' => false,
                'placeholder' => '--- Période ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'secondeMT', type: EntityType::class, options: [
                'class' => Periode::class,                
                'choice_label' => 'libelle',
                'mapped' => false,
                'label' => 'Deuxième mi-temps',
                'required' => false,
                'placeholder' => '--- Période ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parametre::class,
        ]);
    }
}
