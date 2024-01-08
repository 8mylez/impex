<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class SliceExtraction extends ArrayExtractionStrategy
{
    public function __construct(
        private int $offset,
        private ?int $length = null,
        private bool $preserveKeys = false
    ) {
    }

    public function extract(array $data, ConversionContext $context): mixed
    {
        return array_slice($data, $this->offset, $this->length, $this->preserveKeys);
    }
}
