<?php

namespace Dustin\ImpEx\Test\Converter\ListConverter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ListConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ListConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new ListConverter(new Chunker(2), ...($params['flags'] ?? []));
    }
}
