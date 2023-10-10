<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\Numeric\Formatter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class FormatterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new Formatter($params['decimalSeparator'], $params['thousandSeparator'], $params['decimals'], ...($params['flags'] ?? []));
    }
}
