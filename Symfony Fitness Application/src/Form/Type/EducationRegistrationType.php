<?php

namespace App\Form\Type;

use App\Entity\EducationRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\File;

class EducationRegistrationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'disabled' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'disabled' => true,
                 'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'disabled' => true,
                'constraints' => [
                    new Email([
                        'message' => 'common.not_valid.email'
                    ]),
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('phone', NumberType::class, [
                'required' => true,
                'disabled' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                        'message' => 'common.not_valid.phone'
                    ]),
                ]
            ])
            ->add('companyName', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('companyAddress', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('cui', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('registrationNumber', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankName', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankAccount', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'disabled' => true,
                'choices' => [
                    'form_register.card' => EducationRegistration::PAYMENT_TYPE_CARD,
                    'form_register.bank' => EducationRegistration::PAYMENT_TYPE_WIRE
                ],
                'expanded' => false, 
                'multiple' => false, 
                'constraints' => [
                    new Choice([
                        'choices' => [true, false],
                        'message' => 'common.not_blank'
                    ])
                ],
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('paymentAmount', NumberType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('paymentVat', NumberType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'choices' => [
                    'Success' => EducationRegistration::PAYMENT_STATUS_SUCCESS,
                    'Pending' => EducationRegistration::PAYMENT_STATUS_PENDING,
                    'Failed' => EducationRegistration::PAYMENT_STATUS_FAILED
                ],
                'expanded' => false, // Render as a dropdown
                'multiple' => false, // Single selection
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('paymentMessage', TextareaType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('payuPaymentReference', TextType::class, [
                'required' => false,
                'disabled' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('invoicingPerLegalEntity', ChoiceType::class, [
                'disabled' => true,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Choice([
                        'choices' => [true, false],
                        'message' => 'common.not_blank'
                    ])
                ],
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('accordGDPR', ChoiceType::class, [
                'disabled' => true,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => false,
                'multiple' => false,
                'constraints' => [
                    new Choice([
                        'choices' => [true, false],
                        'message' => 'common.not_blank'
                    ])
                ],
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('contract', ChoiceType::class, [
                'disabled' => true,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => false, 
                'multiple' => false,
                'constraints' => [
                    new Choice([
                        'choices' => [true, false],
                        'message' => 'common.not_blank'
                    ])
                ],
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('accordMedia', ChoiceType::class, [
                'disabled' => true,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => false, 
                'multiple' => false, 
                'constraints' => [
                    new Choice([
                        'choices' => [true, false],
                        'message' => 'common.not_blank'
                    ])
                ],
                'attr' => ['class' => 'form-control form-control-solid form-control-lg'],
                'label' => 'form_register.invoice'
            ])
            ->add('certificateFileName', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '3M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'common.not_blank',
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EducationRegistration::class,
        ]);
    }
}
