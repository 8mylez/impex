<?php

namespace Dustin\ImpEx\Sequence\Registry\Validation;

use Dustin\ImpEx\Sequence\Sequence;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SequenceClassValidator extends ConstraintValidator
{
    public static function isSequenceClass(string $value): bool
    {
        if (!is_subclass_of($value, Sequence::class)) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($value);

        return !$reflectionClass->isAbstract();
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if ($value === null || empty($value)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!static::isSequenceClass($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
