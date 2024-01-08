<?php

namespace Dustin\ImpEx\Test\Converter\NullConverter;

use Dustin\ImpEx\Serializer\Converter\NullConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Test\Converter\UnidirectionalConverterTestCase;

class NullConverterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): UnidirectionalConverter
    {
        return new NullConverter(...($params['flags'] ?? []));
    }
}
