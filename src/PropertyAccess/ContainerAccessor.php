<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ContainerAccessor extends Accessor
{
    public static function supportsAccess(mixed $value): bool
    {
        return Type::is($value, Container::class);
    }

    public static function getValueOf(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        if (!is_numeric($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return static::get(intval($field), $value, $path, ...$flags);
    }

    public static function setValueOf(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void
    {
        if ($path === null) {
            $path = $field;
        }

        if (!is_numeric($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }
        }

        static::set(intval($field), $value, $data);
    }

    public static function get(int $index, Container $container, ?string $path, string ...$flags): mixed
    {
        if ($path === null) {
            $path = (string) $index;
        }

        $elements = $container->toArray();

        if (!array_key_exists($index, $elements)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return $elements[$index];
    }

    public static function set(int $index, mixed $value, Container $container): void
    {
        $container->splice($index, 1, [$value]);
    }
}
