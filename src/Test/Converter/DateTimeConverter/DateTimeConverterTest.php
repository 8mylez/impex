<?php

namespace Dustin\ImpEx\Test\Converter\DateTimeConverter;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateTimeConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class DateTimeConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new DateTimeConverter($params['format'], ...($params['flags'] ?? []));
    }
}
