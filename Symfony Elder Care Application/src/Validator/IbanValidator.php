<?php

namespace App\Validator;

use App\Helper\DefaultHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IbanValidator extends ConstraintValidator
{
    /**
     * Validate the given value against the Iban constraint.
     *
     * @param mixed $value The value to validate
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Iban) {
            throw new UnexpectedTypeException($constraint, Iban::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!DefaultHelper::validateIBAN($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
