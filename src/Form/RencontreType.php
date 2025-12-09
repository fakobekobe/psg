<?php

namespace App\Form;

use App\Entity\Calendrier;
use App\Entity\Championnat;
use App\Entity\Rencontre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;

class RencontreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'championnat', type: EntityType::class, options: [
                'class' => Championnat::class,
                'choice_label' => 'nom',
                'label' => false,
                'mapped' => false,
                'placeholder' => '--- Championnat ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'calendrier', type: ChoiceType::class, options: [
                'label' => false,
                'placeholder' => '--- Calendrier ---',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add(child: 'temperature', type:IntegerType::class, options:[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Température',
                ],
            ])
            ->add(child: 'dateHeureRencontre', type:DateTimeType::class, options:[
                'label' => 'Date et Heure'
            ])            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rencontre::class,
        ]);
    }
}
