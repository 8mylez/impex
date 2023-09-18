<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\ImmutableContainer;
use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ContainerAccessor extends Accessor
{
    public static function get(int $field, Container $container, AccessContext $context): mixed
    {
        return ArrayAccessor::get($field, $container->toArray(), $context);
    }

    public static function set(int $field, mixed $value, Container $container, AccessContext $context): void
    {
        if ($field < 0 || $field > count($container)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }

        $container->splice($field, 1, [$value]);
    }

    public static function push(mixed $value, Container $container): void
    {
        $container->add($value);
    }

    public static function merge(mixed $value, Container $data, AccessContext $context): void
    {
        foreach (static::valueToMerge($value) as $key => $valueToMerge) {
            if (!is_numeric($key)) {
                throw InvalidDataException::keyNotNumeric($key);
            }

            if ($context->hasFlag(AccessContext::FLAG_PUSH_ON_MERGE)) {
                static::push($valueToMerge, $data);

                continue;
            }

            $key = intval($key);
            $subContext = new AccessContext(AccessContext::GET, AccessContext::MERGE, new Path($key), ...$context->getFlags());
            $dataValue = static::get($key, $data, new AccessContext(AccessContext::GET, AccessContext::MERGE, new Path($key), AccessContext::FLAG_NULL_ON_ERROR));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                PropertyAccessor::merge('', $dataValue, $valueToMerge, ...$context->getFlags());
                static::set($key, $dataValue, $data, new AccessContext(AccessContext::SET, AccessContext::MERGE, new Path($key), ...$context->getFlags()));
            } else {
                static::set($key, $valueToMerge, $data, new AccessContext(AccessContext::SET, AccessContext::MERGE, new Path($key), ...$context->getFlags()));
            }
        }
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (!Type::is($value, Container::class)) {
            return false;
        }

        return !(\in_array($operation, AccessContext::WRITE_OPERATIONS) && Type::is($value, ImmutableContainer::class));
    }

    protected function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        if (!is_numeric($field)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        return static::get(intval($field), $value, $context);
    }

    protected function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        if (!is_numeric($field)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }

        static::set($field, $value, $data, $context);
    }

    protected function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::push($value, $data);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::merge($value, $data, $context);
    }
}
