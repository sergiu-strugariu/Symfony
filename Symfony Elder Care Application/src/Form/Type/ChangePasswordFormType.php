<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

class ChangePasswordFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('new_password', RepeatedType::class, [
                    'required' => true,
                    'mapped' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match',
                    'constraints' => [
                        new Assert\NotBlank([
                            'message' => 'Please enter your password'
                                ]),
                        new Assert\Regex([
                            'pattern' => '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/',
                            'message' => 'Your password is not valid. It should have 8 characters or more and must contain at least one number, one uppercase character and one lowercase character'
                                ])
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false
        ]);
    }

}
