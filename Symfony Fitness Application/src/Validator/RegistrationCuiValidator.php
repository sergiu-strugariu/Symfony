<?php

namespace App\Validator;

use App\Entity\EducationRegistration;
use App\Helper\DefaultHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RegistrationCuiValidator extends ConstraintValidator
{
    public function validate($registration, Constraint $constraint): void
    {
        if (!$registration instanceof EducationRegistration) {
            throw new UnexpectedValueException($registration, EducationRegistration::class);
        }

        if (!$constraint instanceof RegistrationCui) {
            throw new UnexpectedValueException($constraint, RegistrationCui::class);
        }

        $isInvoicingPerLegalEntity = $registration->isInvoicingPerLegalEntity();

        if ($isInvoicingPerLegalEntity) {
            $cui = $registration->getCui();

            if (!DefaultHelper::validateCIF($cui)) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('cui')
                    ->addViolation();
            }
        }
    }
}