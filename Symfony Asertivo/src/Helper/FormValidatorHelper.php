<?php

namespace App\Helper;

use App\Validator\Cui;
use App\Validator\Iban;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
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
                    'message' => 'Introdu numele'
                ]),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Numele trebuie să aibă cel puțin 2 caractere'
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s-]+$/',
                    'message' => 'Numele poate conține doar litere, spații și cratime.'
                ])
            ],
            'surname' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți prenumele'
                ]),
                new Assert\Length([
                    'min' => 2,
                    'minMessage' => 'Prenumele trebuie să aibă cel puțin 2 caractere'
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s-]+$/',
                    'message' => 'Prenumele poate conține doar litere, spații și cratime.'
                ])
            ],
            'emailAddress' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți o adresă de e-mail'
                ]),
                new Assert\Email([
                    'message' => 'Introduceți o adresă de e-mail validă'
                ])
            ],
            'phone' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți un număr de telefon'
                ]),
                new Assert\Length([
                    'min' => 10,
                    'minMessage' => 'Numărul de telefon trebuie să aibă exact 10 caractere'
                ]),
                new Assert\Length([
                    'max' => 10,
                    'maxMessage' => 'Numărul de telefon trebuie să aibă exact 10 caractere'
                ]),
                new Regex([
                    'pattern' => '/^(?:(?:(?:\+4)?07\d{2}\d{6}|(?:\+4)?(21|31)\d{1}\d{6}|(?:\+4)?((2|3)[3-7]\d{1})\d{6}|(?:\+4)?(8|9)0\d{1}\d{6}))$/',
                    'message' => 'Numărul de telefon trebuie să fie unul valid din România.'
                ]),
            ],
            'message' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți un mesaj'
                ]),
                new Assert\Length([
                    'min' => 200,
                    'minMessage' => 'Mesajul trebuie să conțină cel puțin 200 de caractere'
                ]),
                new Assert\Length([
                    'max' => 1500,
                    'maxMessage' => 'Mesajul poate avea cel mult 1500 de caractere'
                ]),
            ],
            'privacy' => [
                new Assert\IsTrue([
                    'message' => 'Trebuie să fiți de acord cu politica de confidențialitate'
                ])
            ],
            'terms' => [
                new Assert\IsTrue([
                    'message' => 'Trebuie să acceptați termenii și condițiile'
                ])
            ],
            'fileName' => [
                new Assert\NotBlank([
                    'message' => 'Acest câmp este obligatoriu'
                ]),
                new Assert\File([
                    'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                    'mimeTypesMessage' => 'Fișierul trebuie să fie de tip JPEG sau PNG',
                    'maxSize' => '3M'
                ])
            ],
            'fileCv' => [
                new Assert\NotBlank([
                    'message' => 'Trebuie să încărcați un CV'
                ]),
                new Assert\File([
                    'mimeTypes' => ['application/pdf'],
                    'mimeTypesMessage' => 'Fișierul trebuie să fie în format PDF',
                    'maxSize' => '3M'
                ])
            ],
            'star' => [
                new Assert\NotBlank([
                    'message' => 'Acest câmp este obligatoriu'
                ]),
                new Assert\Range([
                    'min' => 1,
                    'max' => 5,
                    'notInRangeMessage' => 'Valoarea trebuie să fie între 1 și 5'
                ])
            ],
            'currentPassword' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți parola curentă'
                ]),
                new UserPassword([
                    'message' => 'Parola introdusă nu este corectă'
                ])
            ],
            'password' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți o parolă'
                ]),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Parola trebuie să aibă cel puțin 8 caractere'
                ]),
            ],
            'repeatPassword' => [
                new Assert\NotBlank([
                    'message' => 'Confirmați parola'
                ]),
                new Callback([$this, 'validatePasswordsMatch']),
            ],
            'cui' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți CUI'
                ]),
                new Assert\Length([
                    'min' => 3,
                    'minMessage' => "CUI-ul trebuie să conțină cel puțin 3 de caractere."
                ]),
                new Cui('CUI invalid.')
            ],
            'iban' => [
                new Assert\NotBlank([
                    'message' => 'Introduceți IBAN-ul'
                ]),
                new Iban('IBAN-ul introdus este invalid')
            ],
            'default' => [
                new Assert\NotBlank([
                    'message' => 'Acest câmp este obligatoriu'
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