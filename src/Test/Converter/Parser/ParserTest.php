<?php

namespace Dustin\ImpEx\Test\Converter\Parser;

use Dustin\ImpEx\Serializer\Converter\Numeric\Parser;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Test\Converter\UnidirectionalConverterTestCase;

class ParserTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new Parser($params['decimalSeparator'], $params['thousandSeparator'], ...($params['flags'] ?? []));
    }
}
