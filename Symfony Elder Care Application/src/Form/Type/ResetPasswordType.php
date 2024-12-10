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
            'invalid_message' => 'Parolele nu coincid',
            'first_options' => ['label' => 'Parola'],
            'second_options' => ['label' => 'Confirma parola'],
            'constraints' => [
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Parola dvs. trebuie să aibă cel puțin {{ limit }} caractere',
                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
