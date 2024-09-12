<?php

namespace App\Form\Type;

use App\Entity\EventSpeaker;
use App\Helper\DefaultHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Regex;

class EventSpeakerForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
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
            ->add('role', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.default.default_field_required'
                    ])
                ]
            ])
            ->add('company', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.default.default_field_required'
                    ])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => DefaultHelper::getStatus()
            ])
            ->add('fileName', FileType::class, [
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
            ->add('twitter', UrlType::class, [
                'required' => false
            ])
            ->add('linkedin', UrlType::class, [
                'required' => false
            ])
            ->add('facebook', UrlType::class, [
                'required' => false
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventSpeaker::class
        ]);
    }
}
