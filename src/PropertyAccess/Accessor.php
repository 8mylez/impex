<?php

namespace Dustin\ImpEx\PropertyAccess;

abstract class Accessor
{
    public const NULL_ON_ERROR = 'null_on_error';

    abstract public static function getSupportedTypes(): array;

    abstract public static function get(string $field, mixed $value, string ...$flags): mixed;

    protected static function hasFlag(string $flag, array $flags): bool
    {
        return \in_array($flag, $flags);
    }
}
