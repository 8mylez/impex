<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\ImmutableContainer;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class ContainerAccessor extends Accessor
{
    public static function get(int|string $field, Container $container, ?string $path, string ...$flags): mixed
    {
        return ArrayAccessor::get($field, $container->toArray(), $path, ...$flags);
    }

    public static function set(int|string $field, mixed $value, Container $container, ?string $path, string ...$flags): void
    {
        $elements = $container->toArray();
        ArrayAccessor::set($field, $value, $elements, $path, ...$flags);

        $container->clear();
        $container->add(...$elements);
    }

    public static function push(mixed $value, Container $container): void
    {
        $container->add($value);
    }

    public function supportsSet(mixed $value): bool
    {
        return Type::is($value, Container::class) && !Type::is($value, ImmutableContainer::class);
    }

    public function supportsGet(mixed $value): bool
    {
        return Type::is($value, Container::class);
    }

    public function supportsPush(mixed $value): bool
    {
        return Type::is($value, Container::class);
    }

    public function getValue(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        if (!is_numeric($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return static::get(intval($field), $value, $path, ...$flags);
    }

    public function setValue(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void
    {
        if ($field === Field::OPERATOR_PUSH) {
            static::push($value, $data);

            return;
        }

        if ($path === null) {
            $path = $field;
        }

        if (!is_numeric($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }
        }

        static::set($field, $value, $data, $path, ...$flags);
    }
}
