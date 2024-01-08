<?php

namespace Dustin\ImpEx\Test\Converter\ConverterMapping;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConverterMapping;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Adder;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ConverterMappingTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new ConverterMapping([
            'foo' => new ConcatConverter(','),
            'bar' => new Adder(1),
        ], ...($params['flags'] ?? []));
    }
}
