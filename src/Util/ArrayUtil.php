<?php

namespace Dustin\ImpEx\Util;

use Dustin\ImpEx\PropertyAccess\Path;

class ArrayUtil
{
    public static function ensure(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return $value === null ? [] : [$value];
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
                if (!isset($current[$field])) {
                    $current[$field] = [];
                }

                $current = &$current[$field];
            }

            $current = $value;
        }

        return $nested;
    }

    public static function nestedToFlat(array $data): array
    {
        return static::nestedToFlatRecursive($data, new Path());
    }

    private static function nestedToFlatRecursive(array $data, Path $path): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $subPath = $path->copy()->add($key);

            if (is_array($value)) {
                $result = array_merge($result, static::nestedToFlatRecursive($value, $subPath));

                continue;
            }

            $result[(string) $subPath] = $value;
        }

        return $result;
    }
}
