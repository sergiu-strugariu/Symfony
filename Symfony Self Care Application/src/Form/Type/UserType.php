<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'dashboard.form.field_mandatory'
                                ])
                    ]
                ])
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'required' => false,
                    'constraints' => [
                        new Assert\Email([
                            'message' => 'form.email.email'
                                ]),
                        new Assert\NotBlank([
                            'message' => 'dashboard.form.field_mandatory'
                                ])
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
                            'min' => 6,
                            'minMessage' =>  'form.password.minlength',
                        ])
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
