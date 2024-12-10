<?php

namespace App\Validator;

use Attribute as AttributeAlias;
use Symfony\Component\Validator\Constraint;

#[AttributeAlias]
class Cui extends Constraint
{
    public string $message = 'CUI invalid.';

    public function __construct(?string $message = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}