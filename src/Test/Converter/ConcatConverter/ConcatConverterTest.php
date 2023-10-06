<?php

namespace Dustin\ImpEx\Test\Converter\ConcatConverter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ConcatConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new ConcatConverter($params['separator'], ...($params['flags'] ?? []));
    }
}
