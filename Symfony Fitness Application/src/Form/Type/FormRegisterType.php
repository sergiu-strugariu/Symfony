<?php

namespace App\Form\Type;

use App\Entity\EducationRegistration;
use App\Validator\Cui;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormRegisterType extends AbstractType
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getFirstName(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'common.custom.first_name'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getLastName(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\s\-]+$/',
                        'message' => 'common.custom.last_name'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getEmail(),
                'constraints' => [
                    new Email([
                        'message' => 'common.not_valid.email'
                    ]),
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'required' => true,
                'data' => null === $user ? '' : $user->getPhoneNumber(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'common.not_blank'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                        'message' => 'common.not_valid.phone'
                    ]),
                ]
            ])

            ->add('invoicingPerLegalEntity', CheckboxType::class, [
                'required' => false
            ])
            ->add('companyName', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getCompanyName(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('companyAddress', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getCompanyAddress(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('cui', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getCui(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ]),
                    new Cui('CUI invalid.')
                ]
            ])
            ->add('registrationNumber', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getRegistrationNumber(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankName', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getBankName(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])
            ->add('bankAccount', TextType::class, [
                'required' => false,
                'data' => null === $user ? '' : $user->getBankAccount(),
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'common.min_message'
                    ])
                ]
            ])

            ->add('paymentMethod', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'form_register.card' => EducationRegistration::PAYMENT_TYPE_CARD,
                    'form_register.bank' => EducationRegistration::PAYMENT_TYPE_WIRE
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => 'card'
            ])

            ->add('accordGDPR', CheckboxType::class, [
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
            ->add('accordMedia', CheckboxType::class, [
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'common.not_blank'
                    ])
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSubmit(PostSubmitEvent $event): void
    {
        $entity = $event->getData();
        $form = $event->getForm();

        if ($entity->isInvoicingPerLegalEntity()) {
            $requiredFields = EducationRegistration::COMPANY_FIELDS;

            foreach ($requiredFields as $field) {

                $accessor = PropertyAccess::createPropertyAccessor();
                $values = $accessor->getValue($entity, $field);

                if (empty($values)) {
                    $form->get($field)->addError(new FormError($this->translator->trans('common.not_blank')));
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => EducationRegistration::class,
            'user' => null
        ]);
    }
}
