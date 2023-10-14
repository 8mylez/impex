<?php

namespace Dustin\ImpEx\Test\Converter\Chunker;

use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ChunkerTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new Chunker($params['chunkSize'], $params['preserveKeys'], ...($params['flags'] ?? []));
    }
}
