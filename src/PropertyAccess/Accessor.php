<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidOperationException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;

abstract class Accessor
{
    abstract public function supports(string $operation, mixed $value): bool;

    abstract public function getValue(string $field, mixed $value, AccessContext $context): mixed;

    abstract public function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void;

    public function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new \RuntimeException(sprintf('%s::pushValue is not implemented.', get_class($this)));
    }

    public function access(?string $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        switch ($context->getOperation()) {
            case AccessContext::GET:
                return $this->getValue($field, $data, $context);

            case AccessContext::SET:
                return $this->setValue($field, $value, $data, $context);

            case AccessContext::PUSH:
                if (!$this->supports(AccessContext::PUSH, $data)) {
                    throw new OperationNotSupportedException(AccessContext::PUSH);
                }

                return $this->pushValue($value, $data, $context);
        }

        throw new InvalidOperationException($context->getOperation());
    }
}
