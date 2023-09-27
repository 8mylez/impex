<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
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
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if ($context->hasFlag(AccessContext::STRICT)) {
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
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return;
        }

        $property = $reflectionObject->getProperty($field);

        if ($property->isStatic()) {
            if ($context->hasFlag(AccessContext::STRICT)) {
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
            $dataValue = static::get($key, $data, $context->createSubContext(AccessOperation::GET, $context->getPath()->copy()->add($key))->removeFlag(AccessContext::STRICT));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                // TODO
                PropertyAccessor::merge('', $dataValue, $valueToMerge, ...$context->getFlags());
                static::set($key, $dataValue, $data, $context->createSubContext(AccessOperation::SET, $context->getPath()->copy()->add($key)));
            } else {
                static::set($key, $valueToMerge, $data, $context->createSubContext(AccessOperation::SET, $context->getPath()->copy()->add($key)));
            }
        }
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (\in_array($operation, [AccessOperation::PUSH, AccessOperation::COLLECT])) {
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
