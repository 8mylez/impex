<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\InvalidOperationException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;

abstract class Accessor
{
    abstract public function supports(string $operation, mixed $value): bool;

    protected static function valueToMerge(mixed $data): \Generator
    {
        if (!static::isMergable($data)) {
            throw InvalidDataException::notMergable($data);
        }

        foreach ($data as $key => $value) {
            yield $key => $value;
        }
    }

    protected static function isMergable(mixed $data): bool
    {
        return is_iterable($data);
    }

    public function access(?string $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        switch ($context->getOperation()) {
            case AccessContext::GET:
                if (!$this->supports(AccessContext::GET, $data)) {
                    throw new OperationNotSupportedException(AccessContext::GET);
                }

                return $this->getValue($field, $data, $context);

            case AccessContext::SET:
                if (!$this->supports(AccessContext::SET, $data)) {
                    throw new OperationNotSupportedException(AccessContext::SET);
                }

                return $this->setValue($field, $value, $data, $context);

            case AccessContext::PUSH:
                if (!$this->supports(AccessContext::PUSH, $data)) {
                    throw new OperationNotSupportedException(AccessContext::PUSH);
                }

                return $this->pushValue($value, $data, $context);

            case AccessContext::MERGE:
                if (!$this->supports(AccessContext::MERGE, $data)) {
                    throw new OperationNotSupportedException(AccessContext::MERGE);
                }

                return $this->mergeValue($value, $data, $context);
        }

        throw new InvalidOperationException($context->getOperation());
    }

    protected function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        throw new \RuntimeException(sprintf('%s::getValue is not implemented.', get_class($this)));
    }

    protected function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new \RuntimeException(sprintf('%s::setValue is not implemented.', get_class($this)));
    }

    protected function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new \RuntimeException(sprintf('%s::pushValue is not implemented.', get_class($this)));
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new \RuntimeException(sprintf('%s::mergeValue is not implemented.', get_class($this)));
    }
}
