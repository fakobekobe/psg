<?php

namespace App\Form;

use App\Entity\Championnat;
use App\Entity\Equipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EquipeType extends AbstractType
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
            ->add(child: 'nom', type:TextType::class, options:[
                'trim' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => "Nom de l'équipe",
                    'autocomplete' => 'off',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            'data_class' => Equipe::class,
        ]);
    }
}
