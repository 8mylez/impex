<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConverterMapping;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class ConverterMappingTest extends BidirectionalConverterTestCase
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
        return new ConverterMapping([
            'foo' => new ConcatConverter(','),
            'bar' => new ArrayConverter(),
        ], ...($params['flags'] ?? []));
    }
}
