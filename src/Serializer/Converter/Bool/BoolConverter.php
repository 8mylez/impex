<?php

namespace Dustin\ImpEx\Serializer\Converter\Bool;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class BoolConverter extends UnidirectionalConverter
{
    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
        ];
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        return boolval($value);
    }
}
