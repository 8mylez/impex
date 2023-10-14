<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

trait NumberConversionTrait
{
    /**
     * Converts a given value into integer or float.
     *
     * @param mixed $value
     */
    protected function convertToNumeric(string|null|int|float|bool $value): int|float
    {
        $value = floatval($value);

        if (floor($value) === $value) {
            $value = intval($value);
        }

        return $value;
    }
}
