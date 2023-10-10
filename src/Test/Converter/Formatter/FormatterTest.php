<?php

namespace Dustin\ImpEx\Test\Converter\Formatter;

use Dustin\ImpEx\Serializer\Converter\Numeric\Formatter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Test\Converter\UnidirectionalConverterTestCase;

class FormatterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new Formatter($params['decimalSeparator'], $params['thousandSeparator'], $params['decimals'], ...($params['flags'] ?? []));
    }
}
