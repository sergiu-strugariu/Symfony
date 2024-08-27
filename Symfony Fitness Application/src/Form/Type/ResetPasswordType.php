<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', RepeatedType::class, [
            'required' => true,
            'type' => PasswordType::class,
            'invalid_message' => 'common.not_valid.password_not_match',
            'first_options' => ['label' => 'common.form_labels.password.first_options'],
            'second_options' => ['label' => 'common.form_labels.password.second_options'],
            'constraints' => [
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'common.min_message',
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
