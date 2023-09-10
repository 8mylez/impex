<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ArrayAccessor extends Accessor
{
    public static function getSupportedTypes(): array
    {
        return [Type::ARRAY];
    }

    public static function get(string $field, mixed $value, string ...$flags): mixed
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        return static::fromArray($field, $value, ...$flags);
    }

    public static function fromArray(int|string $field, array $value, string ...$flags): mixed
    {
        if (!array_key_exists($field, $value)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException((string) $field);
            }

            return null;
        }

        return $value[$field];
    }
}
