<?php

namespace App\Form;

use App\Entity\Joueur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class JoueurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'nom', type:TextType::class, options:[
                'trim' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom complet',
                    'autocomplete' => 'off',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            'data_class' => Joueur::class,
        ]);
    }
}
