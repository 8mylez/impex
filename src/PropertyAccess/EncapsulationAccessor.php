<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\Encapsulation\Exception\PropertyNotExistsException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class EncapsulationAccessor extends Accessor
{
    public static function get(string $field, EncapsulationInterface $value, AccessContext $context): mixed
    {
        if (!$value->has($field)) {
            if (AccessContext::isWriteOperation($context->getRootOperation()) || $context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                return null;
            }

            throw new PropertyNotFoundException($context->getPath());
        }

        return $value->get($field);
    }

    public static function set(string $field, mixed $value, EncapsulationInterface $data, AccessContext $context): void
    {
        try {
            $data->set($field, $value);
        } catch (PropertyNotExistsException $e) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }
    }

    public static function merge(mixed $value, EncapsulationInterface $data, AccessContext $context): void
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

        return Type::is($value, EncapsulationInterface::class);
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
