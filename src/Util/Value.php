<?php

namespace Dustin\ImpEx\Util;

class Value
{
    public static function isNormalized($value): bool
    {
        if (is_scalar($value) || $value === null) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                if (!static::isNormalized($v)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public static function isEmpty(mixed $value, bool $considerWhitespacesAsEmpty = true): bool
    {
        if (is_string($value) && $considerWhitespacesAsEmpty) {
            $value = trim($value);
        }

        if (!empty($value)) {
            return false;
        }

        return !is_numeric($value);
    }
}
