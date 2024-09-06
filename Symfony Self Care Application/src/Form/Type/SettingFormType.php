<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SettingFormType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $builder
            ->add('phone', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['phone']) ? $data['phone'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('helpLine', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['helpLine']) ? $data['helpLine'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['email']) ? $data['email'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ]),
                    new Assert\Email([
                        'message' => 'form.email.email'
                    ])
                ]
            ])
            ->add('twitterLink', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['twitterLink']) ? $data['twitterLink'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('facebookLink', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['facebookLink']) ? $data['facebookLink'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('linkedinLink', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['linkedinLink']) ? $data['linkedinLink'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('instagramLink', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($data['instagramLink']) ? $data['instagramLink'] : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('logo', FileType::class, [
                'required' => empty($data['logo']),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['logo']) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
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
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['footerLogo']) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
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
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($data) {
                            if (empty($data['favicon']) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
                            }
                        }
                    ])
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
