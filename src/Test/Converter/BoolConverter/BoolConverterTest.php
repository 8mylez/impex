<?php

namespace Dustin\ImpEx\Test\Converter\BoolConverter;

use Dustin\ImpEx\Serializer\Converter\Bool\BoolConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Test\Converter\UnidirectionalConverterTestCase;

class BoolConverterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new BoolConverter(...($params['flags'] ?? []));
    }
}
