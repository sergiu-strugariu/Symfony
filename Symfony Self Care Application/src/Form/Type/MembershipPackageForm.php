<?php

namespace App\Form\Type;

use App\Entity\MembershipPackage;
use App\Entity\MembershipPackageTranslation;
use App\Helper\DefaultHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

class MembershipPackageForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MembershipPackageTranslation $translation */
        $translation = $options['translation'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getName() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'mapped' => false,
                'data' => !empty($translation) ? $translation->getDescription() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.message.required'
                    ])
                ]
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d+(\.\d{1,2})?$/',
                        'message' => 'form.price.regex',
                    ])
                ]
            ])
            ->add('discount', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory',
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'form.default.not_in_range',
                    ]),
                ],
                'attr' => ['min' => 0, 'max' => 100]
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
            ->add('popular', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => [
                    'Da' => 1,
                    'Nu' => 0
                ]
            ])
            ->add('fileName', FileType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ])
                ]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    /**
     * @param FormEvent $event
     * @return void
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $entity = $event->getForm()->getData();

        if ($entity->getSlug() == null && isset($data['name'])) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($data['name'])->lower();

            $entity->setSlug($slug);
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembershipPackage::class,
            'translation' => null
        ]);
    }
}
