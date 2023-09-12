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

    public static function getValueOf(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        return static::fromArray($field, $value, $path, ...$flags);
    }

    public static function fromArray(int|string $field, array $value, ?string $path, string ...$flags): mixed
    {
        if ($path === null) {
            $path = (string) $field;
        }

        if (!array_key_exists($field, $value)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return $value[$field];
    }
}
