<?php

namespace App\Validator;

use App\Helper\DefaultHelper;
use App\Validator\Cui;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CuiValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Cui) {
            throw new UnexpectedTypeException($constraint, Cui::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!DefaultHelper::validateCIF($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}