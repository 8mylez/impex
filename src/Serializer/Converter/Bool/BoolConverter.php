<?php

namespace Dustin\ImpEx\Serializer\Converter\Bool;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class BoolConverter extends UnidirectionalConverter
{
    public function convert(mixed $value, ConversionContext $context): bool|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        return boolval($value);
    }
}
