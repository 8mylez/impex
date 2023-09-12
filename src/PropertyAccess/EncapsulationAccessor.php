<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;

class EncapsulationAccessor extends Accessor
{
    public static function getSupportedTypes(): array
    {
        return [EncapsulationInterface::class];
    }

    public static function getValueOf(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        return static::fromEncapsulation($field, $value, $path, ...$flags);
    }

    public static function fromEncapsulation(string $field, EncapsulationInterface $value, ?string $path, string ...$flags): mixed
    {
        if ($path === null) {
            $path = $field;
        }

        if (!$value->has($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return $value->get($field);
    }
}
