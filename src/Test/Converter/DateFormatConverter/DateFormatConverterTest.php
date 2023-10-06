<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateFormatConverter;

class DateFormatConverterTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new DateFormatConverter($params['attributeFormat'], $params['rawFormat'], ...($params['flags'] ?? []));
    }
}
