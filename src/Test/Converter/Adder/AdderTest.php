<?php

namespace Dustin\ImpEx\Test\Converter\Adder;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Adder;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class AdderTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new Adder($params['summand'], ...($params['flags'] ?? []));
    }
}
