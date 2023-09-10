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

    public static function get(string $field, mixed $value, string ...$flags): mixed
    {
        return static::fromEncapsulation($field, $value, ...$flags);
    }

    public static function fromEncapsulation(string $field, EncapsulationInterface $value, string ...$flags): mixed
    {
        if (!$value->has($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($field);
            }

            return null;
        }

        return $value->get($field);
    }
}
