<?php

namespace App\Form\Type;

use App\Entity\EventPartner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EventPartnerForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var EventPartner $partner */
        $partner = $options['data'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'form.name.required'
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'common.select',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'dashboard.form.field_mandatory'
                    ])
                ],
                'choices' => EventPartner::getTypes()
            ])
            ->add('fileName', FileType::class, [
                'required' => empty($partner->getFileName()),
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'form.fileCv.format',
                        'maxSize' => '3M'
                    ]),
                    new Assert\Callback([
                        'callback' => function ($value, ExecutionContextInterface $context) use ($partner) {
                            if (empty($partner->getFileName()) && empty($value)) {
                                $context->buildViolation('dashboard.form.field_mandatory')->addViolation();
                            }
                        }
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
            'data_class' => EventPartner::class
        ]);
    }
}
