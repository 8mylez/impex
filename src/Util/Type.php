<?php

namespace Dustin\ImpEx\Util;

class Type
{
    public const INT = 'int';

    public const BOOL = 'bool';

    public const FLOAT = 'float';

    public const STRING = 'string';

    public const ARRAY = 'array';

    public const OBJECT = 'object';

    public const RESOURCE = 'resource';

    public const NULL = 'null';

    public const CALLABLE = 'callable';

    public const UNKNOWN = 'unknown';

    public const NUMERIC = 'numeric';

    public static function getType($value): string
    {
        if (is_int($value)) {
            return self::INT;
        }

        if (is_bool($value)) {
            return self::BOOL;
        }

        if (is_float($value)) {
            return self::FLOAT;
        }

        if (is_string($value)) {
            return self::STRING;
        }

        if (is_array($value)) {
            return self::ARRAY;
        }

        if (is_object($value)) {
            return self::OBJECT;
        }

        if (is_resource($value)) {
            return self::RESOURCE;
        }

        if (is_null($value)) {
            return self::NULL;
        }

        if (is_callable($value)) {
            return self::CALLABLE;
        }

        return self::UNKNOWN;
    }

    public static function getDebugType($value): string
    {
        return get_debug_type($value);
    }

    public static function is($value, string $type): bool
    {
        if (class_exists($type) || interface_exists($type)) {
            return $value instanceof $type;
        }

        $valueType = self::getType($value);

        if ($type === self::NUMERIC) {
            return self::isNumericType($valueType);
        }

        $isFunction = 'is_'.$type;

        if (function_exists($isFunction)) {
            return $isFunction($value);
        }

        return $type === $valueType;
    }

    public static function isStringConvertable(string $type): bool
    {
        return \in_array($type, [self::INT, self::BOOL, self::FLOAT, self::STRING, self::NULL]);
    }

    public static function isNumericConvertable(string $type): bool
    {
        return \in_array($type, [self::INT, self::FLOAT, self::STRING, self::BOOL, self::NULL]);
    }

    public static function isNumericType(string $type): bool
    {
        return \in_array($type, [self::INT, self::FLOAT]);
    }
}
