<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SettingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $builder
            ->add('phone', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['phone']) ? $data['phone'] : '',
            ])
            ->add('helpLine', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['helpLine']) ? $data['helpLine'] : '',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['email']) ? $data['email'] : '',
                'constraints' => [
                    new Assert\Email([
                        'message' => 'Formatul adresei de email este invalid'
                    ])
                ]
            ])
            ->add('facebookLink', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['facebookLink']) ? $data['facebookLink'] : '',
            ])
            ->add('linkedinLink', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['linkedinLink']) ? $data['linkedinLink'] : '',
            ])
            ->add('instagramLink', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($data['instagramLink']) ? $data['instagramLink'] : '',
            ])
            ->add('authImage', FileType::class, [
                'required' => empty($data['authImage']),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg'],
                        'mimeTypesMessage' => 'Format invalid.',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['authImage']) && empty($value)) {
                                $context->buildViolation('Acest câmp este obligatoriu')->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('logo', FileType::class, [
                'required' => empty($data['logo']),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Format invalid.',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['logo']) && empty($value)) {
                                $context->buildViolation('Acest câmp este obligatoriu')->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('footerLogo', FileType::class, [
                'required' => empty($data['footerLogo']),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Format invalid.',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['footerLogo']) && empty($value)) {
                                $context->buildViolation('Acest câmp este obligatoriu')->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('favicon', FileType::class, [
                'required' => empty($data['favicon']),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/x-icon'],
                        'mimeTypesMessage' => 'Format invalid.',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['favicon']) && empty($value)) {
                                $context->buildViolation('Acest câmp este obligatoriu')->addViolation();
                            }
                        }
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
