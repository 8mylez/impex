<?php

declare(strict_types=1);

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

    public function access(int|string|null $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        switch ($context->getOperation()) {
            case AccessOperation::GET:
                return $this->getValue($field, $data, $context);

            case AccessOperation::SET:
                return $this->setValue($field, $value, $data, $context);

            case AccessOperation::PUSH:
                return $this->pushValue($value, $data, $context);

            case AccessOperation::MERGE:
                return $this->mergeValue($value, $data, $context);

            case AccessOperation::COLLECT:
                return $this->collectValues($data, $context);
        }

        throw new InvalidOperationException($context->getOperation());
    }

    protected function getValue(int|string $field, mixed $value, AccessContext $context): mixed
    {
        throw new OperationNotSupportedException(AccessOperation::GET);
    }

    protected function setValue(int|string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new OperationNotSupportedException(AccessOperation::SET);
    }

    protected function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new OperationNotSupportedException(AccessOperation::PUSH);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        throw new OperationNotSupportedException(AccessOperation::MERGE);
    }

    protected function collectValues(mixed &$data, AccessContext $context): array
    {
        throw new OperationNotSupportedException(AccessOperation::COLLECT);
    }
}
