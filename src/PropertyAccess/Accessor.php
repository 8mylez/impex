<?php

namespace Dustin\ImpEx\PropertyAccess;

abstract class Accessor
{
    public const NULL_ON_ERROR = 'null_on_error';

    abstract public static function supportsAccess(mixed $value): bool;

    abstract public static function getValueOf(string $field, mixed $value, ?string $path, string ...$flags): mixed;

    abstract public static function setValueOf(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void;

    protected static function hasFlag(string $flag, array $flags): bool
    {
        return \in_array($flag, $flags);
    }
}
