<?php

namespace Dustin\ImpEx\Util;

class ArrayUtil
{
    public static function cast(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $value;
    }
}
