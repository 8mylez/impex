<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class IntConverter extends UnidirectionalConverter
{
    public function convert(mixed $value, ConversionContext $context): int|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        return intval($value);
    }
}
