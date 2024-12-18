<?php

namespace App\Form;

use App\Entity\MembershipPackage;
use App\Helper\DefaultHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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

class MembershipPackageFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MembershipPackage $membership */
        $membership = $options['data'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'data' => !empty($membership) ? $membership->getName() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('subName', TextType::class, [
                'required' => true,
                'data' => !empty($membership) ? $membership->getSubName() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ]
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'data' => !empty($membership) ? $membership->getContent() : '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Introdu un mesaj'
                    ])
                ]
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.',
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
                        'message' => 'Acest câmp este obligatoriu.',
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Valoarea trebuie să fie între {{ min }} și {{ max }}.',
                    ]),
                ],
                'attr' => ['min' => 0, 'max' => 100]
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Selectează',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ],
                'choices' => DefaultHelper::getStatus()
            ])
            ->add('isPopular', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Selectează',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ],
                'choices' => [
                    'Da' => 1,
                    'Nu' => 0
                ]
            ])
            ->add('isFree', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Acest câmp este obligatoriu.'
                    ])
                ],
                'choices' => [
                    'Da' => 1,
                    'Nu' => 0
                ]
            ])
            ->add('administrativeModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('medicalModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('physiotherapyModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('infirmaryModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('receptionModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('kitchenModule', CheckboxType::class, [
                'required' => false
            ])
            ->add('fileName', FileType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'],
                        'mimeTypesMessage' => 'Formatul fișierului este nevalid.',
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
            'data_class' => MembershipPackage::class
        ]);
    }
}
