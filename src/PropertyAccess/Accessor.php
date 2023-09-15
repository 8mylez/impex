<?php

namespace Dustin\ImpEx\PropertyAccess;

abstract class Accessor
{
    public const NULL_ON_ERROR = 'null_on_error';

    abstract public function supportsSet(mixed $value): bool;

    abstract public function supportsGet(mixed $value): bool;

    abstract public function supportsPush(mixed $value): bool;

    abstract public function getValue(string $field, mixed $value, ?string $path, string ...$flags): mixed;

    abstract public function setValue(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void;

    protected static function hasFlag(string $flag, array $flags): bool
    {
        return \in_array($flag, $flags);
    }
}
