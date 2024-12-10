<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\CookMenuItem;

class CookMenuItemFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('day', ChoiceType::class, [
                    'required' => true,
                    'placeholder' => 'Selecteaza o optiune...',
                    'label' => 'Zi',
                    'choices' => CookMenuItem::getDays(),
                    'attr' => [
                        'class' => 'form-select form-select-lg form-select-solid'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('breakfast', TextareaType::class, [
                    'required' => true,
                    'label' => 'Mic dejun',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'Mic dejun'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('firstSnack', TextareaType::class, [
                    'required' => true,
                    'label' => 'Gustare (11:00)',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'Gustare (11:00)'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('lunch', TextareaType::class, [
                    'required' => true,
                    'label' => 'Pranz',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'Pranz'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('secondSnack', TextareaType::class, [
                    'required' => true,
                    'label' => 'Mic dejun',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'Gustare (17:00)'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('dinner', TextareaType::class, [
                    'required' => true,
                    'label' => 'Cina',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'Cina'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
                ->add('dz', TextareaType::class, [
                    'required' => true,
                    'label' => 'DZ',
                    'attr' => [
                        'class' => 'form-control form-control-lg form-control-solid',
                        'rows' => '3',
                        'placeholder' => 'DZ'
                    ],
                    'label_attr' => [
                        'class' => 'col-form-label fw-semibold fs-6'
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CookMenuItem::class
        ]);
    }

}
