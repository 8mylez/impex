<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\InvalidOperationException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;

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
            case AccessOperation::GET:
                if (!$this->supports(AccessOperation::GET, $data)) {
                    throw new OperationNotSupportedException(AccessOperation::GET);
                }

                return $this->getValue($field, $data, $context);

            case AccessOperation::SET:
                if (!$this->supports(AccessOperation::SET, $data)) {
                    throw new OperationNotSupportedException(AccessOperation::SET);
                }

                return $this->setValue($field, $value, $data, $context);

            case AccessOperation::PUSH:
                if (!$this->supports(AccessOperation::PUSH, $data)) {
                    throw new OperationNotSupportedException(AccessOperation::PUSH);
                }

                return $this->pushValue($value, $data, $context);

            case AccessOperation::MERGE:
                if (!$this->supports(AccessOperation::MERGE, $data)) {
                    throw new OperationNotSupportedException(AccessOperation::MERGE);
                }

                return $this->mergeValue($value, $data, $context);

            case AccessOperation::COLLECT:
                if (!$this->supports(AccessOperation::COLLECT, $data)) {
                    throw new OperationNotSupportedException(AccessOperation::COLLECT);
                }

                return $this->collectValues($data, $context);
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

    protected function collectValues(mixed &$data, AccessContext $context): array
    {
        throw new \RuntimeException(sprintf('%s::collectValues is not implemented.', get_class($this)));
    }
}
