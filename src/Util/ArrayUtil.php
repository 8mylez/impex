<?php

namespace Dustin\ImpEx\Util;

use Dustin\ImpEx\PropertyAccess\Path;

class ArrayUtil
{
    public static function cast(mixed $value): array
    {
        if (!is_array($value)) {
            if ($value === null) {
                $value = [];
            } else {
                $value = [$value];
            }
        }

        return $value;
    }

    public static function flatToNested(array $data): array
    {
        $nested = [];

        foreach ($data as $key => $value) {
            $path = new Path($key);

            if (count($path) === 0) {
                $nested[$key] = $value;

                continue;
            }

            $current = &$nested;

            foreach ($path as $field) {
                if (is_numeric($field)) {
                    $field = (int) $field;
                }

                if (!isset($current[$field])) {
                    $current[$field] = [];
                }

                $current = &$current[$field];
            }

            $current = $value;
        }

        return $nested;
    }
}
