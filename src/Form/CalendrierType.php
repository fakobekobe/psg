<?php

namespace App\Form;

use App\Entity\Calendrier;
use App\Entity\Championnat;
use App\Entity\Journee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CalendrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'championnat', type: EntityType::class, options: [
                'class' => Championnat::class,
                'choice_label' => 'nom',
                'label' => false,
                'placeholder' => '--- Championnat ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'journee', type: EntityType::class, options: [
                'class' => Journee::class,
                'choice_label' => 'numero',
                'mapped' => false,
                'label' => "Journée",
                'multiple' => true,
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
        $resolver->setDefaults(defaults: [
            'data_class' => Calendrier::class,
        ]);
    }
}
