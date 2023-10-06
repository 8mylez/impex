<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class ArrayConverterTest extends UnidirectionalConverterTestCase
{
    public static function convertProvider(): array
    {
        return static::readJson(__DIR__.'/data.json');
    }

    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new ArrayConverter(...($params['flags'] ?? []));
    }
}
