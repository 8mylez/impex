<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ArrayAccessor extends Accessor
{
    public static function get(int|string $field, array $value, AccessContext $context): mixed
    {
        if (!array_key_exists($field, $value)) {
            if (AccessContext::isWriteOperation($context->getRootOperation()) || $context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
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

    public function supports(string $operation, mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    public function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        return static::get($field, $value, $context);
    }

    public function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        static::set($field, $value, $data);
    }

    public function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::push($value, $data);
    }
}
