<?php

namespace App\Form\Type;

use App\Entity\MenuItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MenuItemFormType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
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
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('link', TextType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
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
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
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
            'data_class' => MenuItem::class
        ]);
    }
}
