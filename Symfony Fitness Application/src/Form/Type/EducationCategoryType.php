<?php

namespace App\Form\Type;

use App\Entity\EducationCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EducationCategoryType extends AbstractType
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

        $slugConstraints = [];

        if (null === $data->getId()) {
            $additionalImageConstraints = [
                new NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
            $imageConstraints = array_merge($imageConstraints, $additionalImageConstraints);
        } else {
            $slugConstraints = [
                new NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
        }

        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getTitle() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('slug', TextType::class, [
                'required' => null === $data->getId() ? false : true,
                'disabled' => null === $data->getId() ? true : false,
                'constraints' => $slugConstraints
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('fileName', FileType::class, [
                'required' => !$data->getId(),
                'mapped' => false,
                'constraints' => $imageConstraints
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $entity = $event->getForm()->getData();

        if (null === $entity->getSlug() && isset($data['title'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['title'])->lower();

            $entity->setSlug($slug);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EducationCategory::class,
            'translation' => null
        ]);
    }
}
