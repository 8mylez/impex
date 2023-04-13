<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

trait NumberConversionTrait
{
    /**
     * Converts a given value into integer or float.
     *
     * @param mixed $value
     *
     * @return int|float
     */
    protected function convertToNumeric($value)
    {
        $value = floatval($value);

        if (floor($value) === $value) {
            $value = intval($value);
        }

        return $value;
    }
}
