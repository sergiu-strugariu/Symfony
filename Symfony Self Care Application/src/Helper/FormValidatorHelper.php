<?php

namespace App\Helper;

use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FormValidatorHelper
{
    const STAR_FIELDS = [
        'generalReview',
        'facilities',
        'maintenanceSupport',
        'cleanliness',
        'dignity',
        'beverages',
        'personnel',
        'activities',
        'security',
        'management',
        'rooms',
        'priceQualityRatio'
    ];

    const AGREE_FIELDS = [
        'nameAgree',
        'myRatingAgree'
    ];

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param TranslatorInterface $translator
     * @param ValidatorInterface $validator
     */
    public function __construct(TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $this->translator = $translator;
        $this->validator = $validator;
    }

    /**
     * @return array[]
     */
    protected function ruleFields(): array
    {
        return [
            'name' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.name.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => $this->translator->trans('form.name.minlength', [], 'messages')
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s-]+$/',
                    'message' => $this->translator->trans('form.name.valid_name', [], 'messages')
                ])
            ],
            'surname' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.surname.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => $this->translator->trans('form.surname.minlength', [], 'messages')
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s-]+$/',
                    'message' => $this->translator->trans('form.surname.valid_name', [], 'messages')
                ])
            ],
            'emailAddress' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.email.required', [], 'messages')
                ]),
                new Assert\Email([
                    'message' => $this->translator->trans('form.email.email', [], 'messages')
                ])
            ],
            'phone' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.phone.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 10,
                    'minMessage' => $this->translator->trans('form.phone.minlength', [], 'messages')
                ]),
                new Assert\Length([
                    'max' => 10,
                    'minMessage' => $this->translator->trans('form.phone.maxlength', [], 'messages')
                ]),
                new Regex([
                    'pattern' => '/^(?:(?:(?:\+4)?07\d{2}\d{6}|(?:\+4)?(21|31)\d{1}\d{6}|(?:\+4)?((2|3)[3-7]\d{1})\d{6}|(?:\+4)?(8|9)0\d{1}\d{6}))$/',
                    'message' => $this->translator->trans('form.phone.phone_ro', [], 'messages')
                ]),
            ],
            'message' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.message.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 200,
                    'minMessage' => $this->translator->trans('form.message.minMessage', [], 'messages')
                ]),
                new Assert\Length([
                    'max' => 1000,
                    'minMessage' => $this->translator->trans('form.message.maxMessage', [], 'messages')
                ]),
            ],
            'shortMessage' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.message.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 100,
                    'minMessage' => $this->translator->trans('form.message.minMessage', [], 'messages')
                ]),
                new Assert\Length([
                    'max' => 250,
                    'minMessage' => $this->translator->trans('form.message.maxMessage', [], 'messages')
                ]),
            ],
            'privacy' => [
                new Assert\IsTrue([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ])
            ],
            'terms' => [
                new Assert\IsTrue([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ])
            ],
            'fileName' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ]),
                new Assert\File([
                    'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                    'mimeTypesMessage' => $this->translator->trans('form.fileCv.format', [], 'messages'),
                    'maxSize' => '3M'
                ])
            ],
            'fileCv' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.fileCv.required', [], 'messages')
                ]),
                new Assert\File([
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => $this->translator->trans('form.fileCv.format', [], 'messages'),
                    'maxSize' => '3M'
                ])
            ],
            'star' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ]),
                new Assert\Range([
                    'min' => 1,
                    'max' => 5,
                    'notInRangeMessage' => $this->translator->trans('form.star.not_in_range_message', [], 'messages')
                ])
            ],
            'currentPassword' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ]),
                new UserPassword([
                    'message' => $this->translator->trans('form.password.passwordMatch', [], 'messages')
                ])
            ],
            'password' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.password.required', [], 'messages')
                ]),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => $this->translator->trans('form.password.minlength', [], 'messages')
                ]),
            ],
            'repeatPassword' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.password.required', [], 'messages')
                ]),
                new Callback([$this, 'validatePasswordsMatch']),
            ],
            'default' => [
                new Assert\NotBlank([
                    'message' => $this->translator->trans('form.default.default_field_required', [], 'messages')
                ])
            ]
        ];
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     * @return void
     */
    public function validatePasswordsMatch($value, ExecutionContextInterface $context): void
    {
        // Get all the data from the context
        $data = $context->getRoot();

        // Get the values for password and repeatPassword
        $password = $data['password'] ?? null;
        $repeatPassword = $value ?? null;

        if ($password !== $repeatPassword) {
            $context->buildViolation($this->translator->trans('form.password.passwordMatch', [], 'messages'))
                ->addViolation();
        }
    }

    /**
     * @param $key
     * @param bool $agree
     * @param bool $star
     * @return array[]
     */
    public function getValidationField($key, bool $agree = false, bool $star = false): array
    {
        $defaultKey = 'default';
        $fields = $this->ruleFields();

        // Check exist stars in fields
        if ($star && in_array($key, self::STAR_FIELDS)) {
            $defaultKey = 'star';
        }

        // Check exist agree in fields
        if ($agree && in_array($key, self::AGREE_FIELDS)) {
            $defaultKey = 'privacy';
        }

        return empty($fields[$key]) ? $fields[$defaultKey] : $fields[$key];
    }

    /**
     * @param array $formData
     * @return array
     */
    public function validate(array $formData): array
    {
        $errors = [];
        $fields = [];

        // Parse and set fields rules
        foreach ($formData as $key => $value) {
            $fields[$key] = $this->getValidationField($key, true, true);
        }

        // Validate fields
        $violations = $this->validator->validate($formData, new Collection(['fields' => $fields]));
        $checkErrors = 0 !== $violations->count();

        // Check exist errors
        if ($checkErrors) {
            // Parse and set errors
            foreach ($violations as $error) {
                $field = trim($error->getPropertyPath(), '[]');
                $errors[$field] = $error->getMessage();
            }
        }

        return [
            'errors' => $errors,
            'checkErrors' => $checkErrors
        ];
    }
}