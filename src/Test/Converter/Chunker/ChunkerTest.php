<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class ChunkerTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new Chunker($params['chunkSize'], $params['preserveKeys'], ...($params['flags'] ?? []));
    }
}
