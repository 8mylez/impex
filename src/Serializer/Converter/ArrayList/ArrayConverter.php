<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class ArrayConverter extends UnidirectionalConverter
{
    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::REINDEX,
        ];
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = (array) $value;

        if ($this->hasFlag(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
