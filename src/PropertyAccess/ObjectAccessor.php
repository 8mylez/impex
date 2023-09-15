<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ObjectAccessor extends Accessor
{
    public static function get(string $field, object $value, AccessContext $context): mixed
    {
        $reflectionObject = new \ReflectionObject($value);
        $getterMethodName = 'get'.\ucfirst($field);

        if (
            $reflectionObject->hasMethod($getterMethodName) &&
            !$reflectionObject->getMethod($getterMethodName)->isStatic()
        ) {
            return $value->$getterMethodName();
        }

        if (!$reflectionObject->hasProperty($field)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        $property->setAccessible(true);

        if ($property->hasType() && !$property->isInitialized($value)) {
            return null;
        }

        return $property->getValue($value);
    }

    public static function set(string $field, mixed $value, object $data, AccessContext $context): void
    {
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
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return;
        }

        $property->setAccessible(true);
        $property->setValue($data, $value);
    }

    public function supports(string $operation, mixed $value): bool
    {
        if ($operation === AccessContext::PUSH) {
            return false;
        }

        return Type::is($value, Type::OBJECT);
    }

    public function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        return static::get($field, $value, $context);
    }

    public function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        static::set($field, $value, $data, $context);
    }
}
