<?php

namespace Dustin\ImpEx\Util;

class Type
{
    public const INT = 'integer';

    public const BOOL = 'boolean';

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
        $type = gettype($value);

        switch ($type) {
            case 'integer':
                return self::INT;
            case 'boolean':
                return self::BOOL;
            case 'double':
                return self::FLOAT;
            case 'string':
                return self::STRING;
            case 'array':
                return self::ARRAY;
            case 'object':
                return self::OBJECT;
            case 'resource':
            case 'resource (closed)':
                return self::RESOURCE;
            case 'NULL':
                return self::NULL;
            default:
                return is_callable($value) ? self::CALLABLE : self::UNKNOWN;
        }
    }

    public static function getDebugType($value): string
    {
        $type = self::getType($value);

        return $type === self::OBJECT ? get_class($value) : $type;
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

        return $valueType === $type;
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
