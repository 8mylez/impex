<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateFormatConverter;

class DateFormatConverterTest extends BidirectionalConverterTestCase
{
    public static function normalizeProvider(): array
    {
        return static::readJson(__DIR__.'/normalize.json');
    }

    public static function denormalizeProvider(): array
    {
        return static::readJson(__DIR__.'/denormalize.json');
    }

    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        return new DateFormatConverter($params['attributeFormat'], $params['rawFormat'], ...($params['flags'] ?? []));
    }
}
