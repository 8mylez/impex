<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;

class ContainerAccessor extends Accessor
{
    public static function getSupportedTypes(): array
    {
        return [Container::class];
    }

    public static function get(string $field, mixed $value, string ...$flags): mixed
    {
        if (!is_numeric($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($field);
            }

            return null;
        }

        return static::fromContainer(intval($field), $value, ...$flags);
    }

    public static function fromContainer(int $index, Container $container, string ...$flags): mixed
    {
        $elements = $container->toArray();

        if (!array_key_exists($index, $elements)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($index);
            }

            return null;
        }

        return $elements[$index];
    }
}
