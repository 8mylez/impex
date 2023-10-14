<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class ArrayConverter extends UnidirectionalConverter
{
    public const INCLUDE_ARRAYS = 'include_arrays';

    public function convert(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!is_array($value) || $this->hasFlags(self::INCLUDE_ARRAYS)) {
            if ($value === null) {
                $value = [];
            } else {
                $value = [$value];
            }
        }

        if ($this->hasFlags(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
