<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateTimeConverter;

class DateTimeConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new DateTimeConverter($params['format'], ...($params['flags'] ?? []));
    }
}
