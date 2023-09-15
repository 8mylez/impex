<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\ImmutableContainer;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ContainerAccessor extends Accessor
{
    public static function get(int|string $field, Container $container, AccessContext $context): mixed
    {
        return ArrayAccessor::get($field, $container->toArray(), $context);
    }

    public static function set(int $field, mixed $value, Container $container, AccessContext $context): void
    {
        if ($field <= 0 || $field > count($container)) {
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

    public function supports(string $operation, mixed $value): bool
    {
        if (!Type::is($value, Container::class)) {
            return false;
        }

        return !(\in_array($operation, AccessContext::WRITE_OPERATIONS) && Type::is($value, ImmutableContainer::class));
    }

    public function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        if (!is_numeric($field)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        return static::get(intval($field), $value, $context);
    }

    public function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        if (!is_numeric($field)) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }

        static::set($field, $value, $data, $context);
    }

    public function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::push($value, $data);
    }
}
