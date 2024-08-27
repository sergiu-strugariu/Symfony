<?php

namespace App\Form\Type;

use App\Entity\TeamMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeamMemberType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $translation = $options['translation'];

        $imageConstraints = [
            new Assert\File([
                'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                'mimeTypesMessage' => 'common.not_blank',
                'maxSize' => '3M'
            ])
        ];
        if (null === $data->getId()) {
            $additionalImageConstraints = [
                new Assert\NotBlank([
                    'message' => 'common.not_blank'
                ])
            ];
            $imageConstraints = array_merge($imageConstraints, $additionalImageConstraints);
        }

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
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('slug', TextType::class, [
                'required' => null === $data->getId() ? false : true,
                'disabled' => null === $data->getId() ? true : false,
                'constraints' => $slugConstraints
            ])
            ->add('specialization', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getSpecialization() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ],
                'label' => 'Description <span class="text-danger">*</span>', // Custom label with HTML
                'label_html' => true, // Allows HTML in the label
                'label_attr' => [
                    'class' => 'form-label fw-semibold fs-6' // Custom CSS classes for the label
                ],
            ])

            ->add('image', FileType::class, [
                'required' => !$data->getId(),
                'mapped' => false,
                'constraints' => $imageConstraints
            ]);

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

        if (null === $entity->getSlug() && isset($data['name'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['name'])->lower();

            $entity->setSlug($slug);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TeamMember::class,
            'translation' => null
        ]);
    }
}
