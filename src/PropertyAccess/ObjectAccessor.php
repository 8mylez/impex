<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ObjectAccessor extends Accessor
{
    public static function get(string $field, object $value, ?string $path, string ...$flags): mixed
    {
        if ($path === null) {
            $path = $field;
        }

        $reflectionObject = new \ReflectionObject($value);
        $getterMethodName = 'get'.\ucfirst($field);

        if (
            $reflectionObject->hasMethod($getterMethodName) &&
            !$reflectionObject->getMethod($getterMethodName)->isStatic()
        ) {
            return $value->$getterMethodName();
        }

        if (!$reflectionObject->hasProperty($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        $property->setAccessible(true);

        if ($property->hasType() && !$property->isInitialized($value)) {
            return null;
        }

        return $property->getValue($value);
    }

    public static function set(string $field, mixed $value, object $data, ?string $path, string ...$flags): void
    {
        if ($path === null) {
            $path = $field;
        }

        $reflectionObject = new \ReflectionObject($data);
        $setterMethodName = 'set'.\ucfirst($field);

        if (
            $reflectionObject->hasMethod($setterMethodName) &&
            !$reflectionObject->getMethod($setterMethodName)->isStatic()
        ) {
            $data->$setterMethodName($value);

            return;
        }

        if (!$reflectionObject->hasProperty($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return;
        }

        $property->setAccessible(true);
        $property->setValue($data, $value);
    }

    public function supportsSet(mixed $value): bool
    {
        return Type::is($value, Type::OBJECT);
    }

    public function supportsGet(mixed $value): bool
    {
        return Type::is($value, Type::OBJECT);
    }

    public function supportsPush(mixed $value): bool
    {
        return false;
    }

    public function getValue(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        return static::get($field, $value, $path, ...$flags);
    }

    public function setValue(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void
    {
        static::set($field, $value, $data, $path, ...$flags);
    }
}
