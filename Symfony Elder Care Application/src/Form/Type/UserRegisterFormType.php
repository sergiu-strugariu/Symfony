<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserRegisterFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Camp obligatoriu necompletat'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Acest câmp trebuie să aiba cel putin {{ limit }} caractere.'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'Nume invalid'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Camp obligatoriu necompletat'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Acest câmp trebuie să aiba cel putin {{ limit }} caractere.'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'Prenume invalid'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new Email([
                        'message' => 'Te rugam sa introduci o adresa de email valida'
                    ]),
                    new NotBlank([
                        'message' => 'Camp obligatoriu necompletat'
                    ])
                ]
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Camp obligatoriu necompletat'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                        'message' => 'Numar de telefon invalid'
                    ]),
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'required' => true,
                'type' => PasswordType::class,
                'invalid_message' => 'Parolele nu coincid',
                'first_options' => ['label' => 'Parola'],
                'second_options' => ['label' => 'Confirma parola'],
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Parola dvs. trebuie să aibă cel puțin {{ limit }} caractere',
                    ])
                ]
            ])
            ->add('terms', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue([
                        'message' => 'Trebuie să fiți de acord cu politica de confidențialitate'
                    ])
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

}
