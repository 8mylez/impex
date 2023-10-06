<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\Bool\BoolConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class BoolConverterTest extends UnidirectionalConverterTestCase
{
    public static function convertProvider(): array
    {
        return static::readJson(__DIR__.'/data.json');
    }

    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new BoolConverter(...($params['flags'] ?? []));
    }
}
