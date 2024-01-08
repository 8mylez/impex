<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\ArrayUtil;

class ArrayConverter extends UnidirectionalConverter
{
    public const ENCASE_ARRAYS = 'encase_arrays';

    public function convert(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!is_array($value)) {
            $value = ArrayUtil::ensure($value);
        } elseif ($this->hasFlags(self::ENCASE_ARRAYS)) {
            $value = [$value];
        }

        if ($this->hasFlags(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
