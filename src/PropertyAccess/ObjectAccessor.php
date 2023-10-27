<?php

declare(strict_types=1);

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
            $dataValue = static::get($key, $data, $context->subContext(AccessOperation::GET, new Path([$key]))->removeFlag(AccessContext::STRICT));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                $context->subContext(AccessOperation::MERGE, new Path([$key]))->access([], $dataValue, $valueToMerge);
                static::set($key, $dataValue, $data, $context->subContext(AccessOperation::SET, new Path([$key])));
            } else {
                static::set($key, $valueToMerge, $data, $context->subContext(AccessOperation::SET, new Path([$key])));
            }
        }
    }

    public static function has(string $property, object $data): bool
    {
        $reflectionObject = new \ReflectionObject($data);

        if (!$reflectionObject->hasProperty($property)) {
            return false;
        }

        return !$reflectionObject->getProperty($property)->isStatic();
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (\in_array($operation, [AccessOperation::PUSH, AccessOperation::COLLECT])) {
            return false;
        }

        return Type::is($value, Type::OBJECT);
    }

    protected function getValue(int|string $field, mixed $value, AccessContext $context): mixed
    {
        return static::get($field, $value, $context);
    }

    protected function setValue(int|string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        static::set($field, $value, $data, $context);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::merge($value, $data, $context);
    }

    protected function hasProperty(int|string $field, mixed $data, AccessContext $context): bool
    {
        if (is_int($field)) {
            return false;
        }

        return static::has($field, $data);
    }
}
