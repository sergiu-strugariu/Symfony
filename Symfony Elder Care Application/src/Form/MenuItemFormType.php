<?php

namespace App\Form;

use App\Entity\MenuItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class MenuItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('parentId', HiddenType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('linkText', TextType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Acest câmp este obligatoriu'
                    ])
                ]
            ])
            ->add('link', TextType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Acest câmp este obligatoriu'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('cssClass', TextType::class, [
                'required' => false
            ])
            ->add('icon', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Format invalid.',
                        'maxSize' => '3M'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuItem::class,
        ]);
    }
}
