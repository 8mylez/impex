<?php

namespace Dustin\ImpEx\Test\Converter\ListConverter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ElementConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\SizeChunkStrategy;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ElementConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new ElementConverter(new Chunker(new SizeChunkStrategy(2)), ...($params['flags'] ?? []));
    }
}
