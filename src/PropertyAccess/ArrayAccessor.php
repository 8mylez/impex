<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Util\Type;

class ArrayAccessor extends Accessor
{
    public static function get(int|string $field, array $value, AccessContext $context): mixed
    {
        if (!array_key_exists($field, $value)) {
            if (AccessOperation::isWriteOperation($context->getRootOperation()) || $context->hasFlag(AccessContext::NULL_ON_ERROR)) {
                return null;
            }

            throw new PropertyNotFoundException($context->getPath());
        }

        return $value[$field];
    }

    public static function set(int|string $field, mixed $value, array &$data): void
    {
        $data[$field] = $value;
    }

    public static function push(mixed $value, array &$data): void
    {
        $data[] = $value;
    }

    public static function merge(mixed $value, array &$data, AccessContext $context): void
    {
        foreach (static::valueToMerge($value) as $key => $valueToMerge) {
            $dataValue = static::get($key, $data, new AccessContext(AccessOperation::GET, AccessOperation::MERGE, new Path($key), AccessContext::NULL_ON_ERROR));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                PropertyAccessor::merge('', $dataValue, $valueToMerge, ...$context->getFlags());
                static::set($key, $dataValue, $data);
            } else {
                if (is_numeric($key) && $context->hasFlag(AccessContext::PUSH_ON_MERGE)) {
                    static::push($valueToMerge, $data);
                } else {
                    static::set($key, $valueToMerge, $data);
                }
            }
        }
    }

    public static function collect(array $data): array
    {
        return $data;
    }

    public function supports(string $operation, mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    protected function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        return static::get($field, $value, $context);
    }

    protected function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        static::set($field, $value, $data);
    }

    protected function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::push($value, $data);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::merge($value, $data, $context);
    }

    protected function collectValues(mixed &$data, AccessContext $context): array
    {
        return static::collect($data);
    }
}
