<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Contracts\Translation\TranslatorInterface;


class ValidationConstraints
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getConstraints(): array
    {
        return [
            'firstName' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s]+$/',
                    'message' => $this->translator->trans('common.custom.first_name')
                ])
            ],
            'lastName' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ]),
                new Regex([
                    'pattern' => '/^[a-zA-Z\s]+$/',
                    'message' => $this->translator->trans('common.custom.last_name')
                ])
            ],
            'email' => [
                new Email([
                    'message' => $this->translator->trans('common.not_valid.email')
                ]),
                new NotBlank([
                    'message' => $this->translator->trans('common.not_valid.email')
                ])
            ],
            'phoneNumber' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_valid.phone')
                ]),
                new Regex([
                    'pattern' => '/^(\+4|)?(07[0-8]{1}[0-9]{1}|02[0-9]{2}|03[0-9]{2}){1}?(\s|\.|\-)?([0-9]{3}(\s|\.|\-|)){2}$/',
                    'message' => $this->translator->trans('common.not_valid.phone')
                ]),
            ],
        ];
    }

    public function getCompanyConstraints()
    {
        return [
            'companyName' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ])
            ],
            'companyAddress' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ])
            ],
            'cui' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ]),
                new Cui('CUI invalid.')
            ],
            'registrationNumber' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ])
            ],
            'bankName' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ])
            ],
            'bankAccount' => [
                new NotBlank([
                    'message' => $this->translator->trans('common.not_blank')
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => $this->translator->trans('common.min_message'),
                ])
            ],
        ];
    }
}
