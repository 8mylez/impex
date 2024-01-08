<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class ElementExtraction extends ArrayExtractionStrategy
{
    public const FIRST = 'impex__first';

    public const LAST = 'impex__last';

    public function __construct(private int|string $key)
    {
    }

    public function extract(array $data, ConversionContext $context): mixed
    {
        if ($this->key === self::FIRST) {
            return array_values($data)[0] ?? null;
        }

        if ($this->key === self::LAST) {
            return array_values($data)[count($data) - 1] ?? null;
        }

        return $data[$this->key] ?? null;
    }
}
