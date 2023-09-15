<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\Encapsulation\Exception\PropertyNotExistsException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class EncapsulationAccessor extends Accessor
{
    public static function get(string $field, EncapsulationInterface $value, ?string $path, string ...$flags): mixed
    {
        if ($path === null) {
            $path = $field;
        }

        if (!$value->has($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }

            return null;
        }

        return $value->get($field);
    }

    public static function set(string $field, mixed $value, EncapsulationInterface $data, ?string $path, string ...$flags): void
    {
        if ($path === null) {
            $path = $field;
        }

        try {
            $data->set($field, $value);
        } catch (PropertyNotExistsException $e) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($path);
            }
        }
    }

    public function supportsSet(mixed $value): bool
    {
        return Type::is($value, EncapsulationInterface::class);
    }

    public function supportsGet(mixed $value): bool
    {
        return Type::is($value, EncapsulationInterface::class);
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
