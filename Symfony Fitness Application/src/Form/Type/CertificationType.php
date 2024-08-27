<?php

namespace App\Form\Type;

use App\Entity\Certification;
use App\Entity\CertificationCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CertificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $translation = $options['translation'];

        $imageConstraints = [
            new File([
                'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                'mimeTypesMessage' => 'common.not_blank',
                'maxSize' => '3M'
            ])
        ];

        if (null === $data->getId()) {
            $additionalImageConstraints = [
                new NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
            $imageConstraints = array_merge($imageConstraints, $additionalImageConstraints);
        }

        $builder
            ->add('certificateCategory', EntityType::class, [
                'class' => CertificationCategory::class,
                'choice_label' => function (CertificationCategory $certificationCategory) use ($translation) {
                    return $certificationCategory->getTranslation('ro')->getName();
                },
                'mapped' => true,
                'required' => true
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('level', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getLevel() : '',
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'max' => 200,
                        'maxMessage' => 'common.max_message'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'required' => !$data->getId(),
                'mapped' => false,
                'constraints' => $imageConstraints
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Certification::class,
            'translation' => null
        ]);
    }
}
