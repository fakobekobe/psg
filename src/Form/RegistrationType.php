<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(child: 'nom', type: TextType::class, options: [
                'label' => false,
                'trim' => true,
                'attr' => [
                    'placeholder' => 'Nom complet',
                    'class' => 'form-control-user',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add(child: 'email', type: EmailType::class, options: [
                'label' => false,
                'trim' => true,
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'form-control-user',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ]
            ])
            ->add(child: 'plainPassword', type: RepeatedType::class, options: [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank(options: [
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length(exactly: [
                            'min' => 12,
                            'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} charactères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                    ],
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                    ],
                ],
                'second_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Confirmez mot de passe',
                    ],
                ],
                'invalid_message' => 'Le mot de passe est différent',
                'mapped' => false,
            ])
            ->add(child: 'actif', type: CheckboxType::class, options: [
                'label' => "Actif",
                'required' => false,
                'attr' => [                    
                    'class' => 'ml-1',                    
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            //'data_class' => Utilisateur::class,
        ]);
    }
}
