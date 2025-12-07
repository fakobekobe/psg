<?php

namespace App\Form;

use App\Entity\Saison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SaisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'libelle', type:TextType::class, options:[
                'trim' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Saison Ex: 2025-2026',
                    'autocomplete' => 'off',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            'data_class' => Saison::class,
        ]);
    }
}
