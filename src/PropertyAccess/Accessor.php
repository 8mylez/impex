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

        if (is_iterable($data)) {
            foreach ($data as $key => $value) {
                yield $key => $value;
            }

            return;
        }

        $reflectionObject = new \ReflectionObject($data);

        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            $name = $reflectionProperty->getName();
            $getterMethodName = 'get'.\ucfirst($name);

            if (
                $reflectionObject->hasMethod($getterMethodName) &&
                !$reflectionObject->getMethod($getterMethodName)->isStatic()
            ) {
                yield $name => $data->$getterMethodName();

                continue;
            }

            $reflectionProperty->setAccessible(true);

            if ($reflectionProperty->hasType() && !$reflectionProperty->isInitialized($data)) {
                yield $name => null;

                continue;
            }

            yield $name => $reflectionProperty->getValue($data);
        }
    }

    protected static function isMergable(mixed $data): bool
    {
        return is_iterable($data) || is_object($data);
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

            case AccessOperation::HAS:
                return $this->hasProperty($field, $data, $context);
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

    protected function hasProperty(int|string $field, mixed $data, AccessContext $context): bool
    {
        throw new OperationNotSupportedException(AccessOperation::HAS);
    }
}
