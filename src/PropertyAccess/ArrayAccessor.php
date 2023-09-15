<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ArrayAccessor extends Accessor
{
    public static function get(int|string $field, array $value, ?string $path, string ...$flags): mixed
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

    public static function set(int|string $field, mixed $value, array &$data, ?string $path, string ...$flags): void
    {
        if ($field === Field::OPERATOR_PUSH) {
            static::push($value, $data);

            return;
        }

        $data[$field] = $value;
    }

    public static function push(mixed $value, array &$data): void
    {
        $data[] = $value;
    }

    public function supportsSet(mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    public function supportsGet(mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    public function supportsPush(mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    public function getValue(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        return static::get($field, $value, $path, ...$flags);
    }

    public function setValue(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void
    {
        if (is_numeric($field)) {
            $field = intval($field);
        }

        static::set($field, $value, $data, $path, ...$flags);
    }
}
