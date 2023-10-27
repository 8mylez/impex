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

    public const ITERABLE = 'iterable';

    private const DATA_TYPES = [
        self::INT,
        self::BOOL,
        self::FLOAT,
        self::STRING,
        self::ARRAY,
        self::OBJECT,
        self::RESOURCE,
        self::NULL,
        self::CALLABLE,
    ];

    public static function getType($value): string
    {
        foreach (self::DATA_TYPES as $type) {
            $isType = 'is_'.$type;

            if ($isType($value)) {
                return $type;
            }
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

        $isFunction = 'is_'.$type;

        if ($type !== self::NUMERIC && function_exists($isFunction)) {
            return $isFunction($value);
        }

        $valueType = self::getType($value);

        if ($type === self::NUMERIC) {
            return self::isNumericType($valueType);
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
