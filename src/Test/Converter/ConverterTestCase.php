<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use PHPUnit\Framework\TestCase;

abstract class ConverterTestCase extends TestCase
{
    protected static function readJson(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    protected function createConversionContext(string $direction): ConversionContext
    {
        return new ConversionContext(
            new \stdClass(),
            new Path(['someAttribute']),
            'someAttribute',
            $direction,
            [],
            []
        );
    }
}
