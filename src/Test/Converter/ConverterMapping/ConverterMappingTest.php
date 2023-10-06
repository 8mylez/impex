<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConverterMapping;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class ConverterMappingTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new ConverterMapping([
            'foo' => new ConcatConverter(','),
            'bar' => new ArrayConverter(),
        ], ...($params['flags'] ?? []));
    }
}
