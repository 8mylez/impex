<?php

namespace Dustin\ImpEx\Test\Converter\Multiplier;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Multiplier;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class MultiplierTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new Multiplier($params['factor'], ...($params['flags'] ?? []));
    }
}
