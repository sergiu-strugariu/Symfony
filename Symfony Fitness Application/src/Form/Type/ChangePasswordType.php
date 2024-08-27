<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class,[
                "mapped" => false,
                "constraints" => [
                    new UserPassword()
                ]
            ])
            ->add('newPassword', RepeatedType::class, [
                'required' => true,
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'common.not_valid.password_not_match',
                'first_options'  => ['label' => 'common.form_labels.password.first_options'],
                'second_options' => ['label' => 'common.form_labels.password.second_options'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'common.min_message',
                    ])
                ],
            ])
        ;
    }

    public function getBlockPrefix() { return ''; }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false
        ]);
    }
}
