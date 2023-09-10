<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ObjectAccessor extends Accessor
{
    public static function getSupportedTypes(): array
    {
        return [Type::OBJECT];
    }

    public static function get(string $field, mixed $value, string ...$flags): mixed
    {
        return static::fromObject($field, $value, ...$flags);
    }

    public static function fromObject(string $field, object $value, string ...$flags): mixed
    {
        $reflectionObject = new \ReflectionObject($value);

        if (!$reflectionObject->hasProperty($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($field);
            }

            return null;
        }

        $property = $reflectionObject->getProperty($field);
        $property->setAccessible(true);

        if ($property->hasType() && !$property->isInitialized($value)) {
            return null;
        }

        return $property->getValue($value);
    }
}
