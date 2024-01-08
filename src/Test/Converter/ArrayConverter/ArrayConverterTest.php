<?php

namespace Dustin\ImpEx\Test\Converter\ArrayConverter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Test\Converter\UnidirectionalConverterTestCase;

class ArrayConverterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new ArrayConverter(...($params['flags'] ?? []));
    }
}
