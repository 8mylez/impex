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

    public static function merge(mixed $value, object $data, AccessContext $context): void
    {
        foreach (static::valueToMerge($value) as $key => $valueToMerge) {
            $dataValue = static::get($key, $data, $context->createSubContext(AccessContext::GET, new Path($key)));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                PropertyAccessor::merge('', $dataValue, $valueToMerge, ...$context->getFlags());
                static::set($key, $dataValue, $data, $context->createSubContext(AccessContext::SET, new Path($key)));
            } else {
                static::set($key, $valueToMerge, $data, $context->createSubContext(AccessContext::SET, new Path($key)));
            }
        }
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (\in_array($operation, [AccessContext::PUSH, AccessContext::COLLECT])) {
            return false;
        }

        return Type::is($value, Type::OBJECT);
    }

    protected function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        return static::get($field, $value, $context);
    }

    protected function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        static::set($field, $value, $data, $context);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::merge($value, $data, $context);
    }
}
