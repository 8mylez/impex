<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class ArrayConverter extends UnidirectionalConverter
{
    public function __construct(private ArrayConversionStrategy $strategy)
    {
    }

    public function convert(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->strategy->convert($value, $context);

        if ($this->hasFlags(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
