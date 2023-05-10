<?php

namespace Dustin\ImpEx\Util;

class Value
{
    public static function isNormalized($value): bool
    {
        if (is_object($value) || is_resource($value) || is_callable($value)) {
            return false;
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                if (!static::isNormalized($v)) {
                    return false;
                }
            }
        }

        return true;
    }
}
