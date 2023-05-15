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
}
