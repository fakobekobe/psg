<?php

namespace App\Form;

use App\Entity\Championnat;
use App\Entity\EquipeSaison;
use App\Entity\Joueur;
use App\Entity\Saison;
use App\Entity\Transfert;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransfertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'saison', type: EntityType::class, options: [
                'class' => Saison::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('e')                        
                        ->orderBy('e.id', 'DESC')                        
                        ;
                },
                'choice_label' => 'libelle',
                'label' => false,
                'mapped' => false,
                'placeholder' => '--- Saison ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])    
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
            ->add(child: 'equipe', type: ChoiceType::class, options: [
                'label' => false,
                'placeholder' => '--- Equipe ---',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])                    
            ->add(child: 'joueur', type: EntityType::class, options: [
                'class' => Joueur::class,
                'choice_label' => 'nom',
                'label' => false,
                'placeholder' => '--- Joueur ---',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank,
                ],
            ])
            ->add('Envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transfert::class,
        ]);
    }
}
