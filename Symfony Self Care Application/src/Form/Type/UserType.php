<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.name.required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'form.name.minlength'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZăâîșțĂÂÎȘȚ\s-]+$/',
                        'message' => 'form.name.valid_name'
                    ])
                ]
            ])
            ->add('surname', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.surname.required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'form.surname.minlength'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-ZăâîșțĂÂÎȘȚ\s-]+$/',
                        'message' => 'form.surname.valid_name'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.phone.required'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'form.phone.minlength'
                    ]),
                    new Assert\Length([
                        'max' => 10,
                        'minMessage' => 'form.phone.maxlength'
                    ]),
                    new Regex([
                        'pattern' => '/^(?:(?:(?:\+4)?07\d{2}\d{6}|(?:\+4)?(21|31)\d{1}\d{6}|(?:\+4)?((2|3)[3-7]\d{1})\d{6}|(?:\+4)?(8|9)0\d{1}\d{6}))$/',
                        'message' => 'form.phone.phone_ro'
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new Assert\Email([
                        'message' => 'form.email.email'
                    ]),
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('profilePicture', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ])
                ]
            ])
            ->add('enabled', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => [
                    'Enabled' => 1,
                    'Disabled' => 0
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'required' => false,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'form.password.passwordMatch',
                'options' => ['attr' => ['placeholder' => '******']],
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'form.password.minlength',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
