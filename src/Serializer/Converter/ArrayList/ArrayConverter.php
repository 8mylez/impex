<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class ArrayConverter extends UnidirectionalConverter
{
    public const INCLUDE_ARRAYS = 'include_arrays';

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!is_array($value) || $this->hasFlag(self::INCLUDE_ARRAYS)) {
            $value = [$value];
        }

        if ($this->hasFlag(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
