<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\Encapsulation\Exception\PropertyNotExistsException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Util\Type;

class EncapsulationAccessor extends Accessor
{
    public static function get(string $field, EncapsulationInterface $value, AccessContext $context): mixed
    {
        if (!$value->has($field)) {
            if ($context->hasFlag(AccessContext::STRICT) && !AccessOperation::isWriteOperation($context->getRootOperation())) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        return $value->get($field);
    }

    public static function set(string $field, mixed $value, EncapsulationInterface $data, AccessContext $context): void
    {
        try {
            $data->set($field, $value);
        } catch (PropertyNotExistsException $e) {
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }
    }

    public static function merge(mixed $value, EncapsulationInterface $data, AccessContext $context): void
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

    public static function has(string $field, EncapsulationInterface $data): bool
    {
        return $data->has($field);
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (\in_array($operation, [AccessOperation::PUSH, AccessOperation::COLLECT])) {
            return false;
        }

        return Type::is($value, EncapsulationInterface::class);
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
        return static::has((string) $field, $data);
    }
}
