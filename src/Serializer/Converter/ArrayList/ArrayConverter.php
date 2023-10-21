<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\ArrayUtil;

class ArrayConverter extends UnidirectionalConverter
{
    public const INCLUDE_ARRAYS = 'include_arrays';

    public function convert(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!is_array($value)) {
            if ($this->hasFlags(self::INCLUDE_ARRAYS)) {
                $value = [$value];
            } else {
                $value = ArrayUtil::ensure($value);
            }
        }

        if ($this->hasFlags(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
