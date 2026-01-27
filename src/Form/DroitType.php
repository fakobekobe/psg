<?php

namespace App\Form;

use App\Entity\Droit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DroitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'nom', type:TextType::class, options:[
                'trim' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Droit',
                    'autocomplete' => 'off',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Droit::class,
        ]);
    }
}
