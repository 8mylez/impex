<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use PHPUnit\Framework\TestCase;

class ArrayConverterTest extends TestCase
{
    /**
     * @dataProvider arrayConverterProvider
     */
    public function testArrayConverter($input, $expectedResult, array $flags = [])
    {
        $converter = new ArrayConverter(...$flags);

        $result = $converter->convert($input, new Encapsulation(), '', '');
        $this->assertSame($result, $expectedResult);
    }

    public function arrayConverterProvider()
    {
        return [
            ['foo', ['foo']],
            [null, []],
            [null, null, [AttributeConverter::SKIP_NULL]],
            [['foo' => 'bar'], [0 => 'bar'], [ArrayConverter::REINDEX]],
            [123, [123]],
            [true, [true]],
        ];
    }
}
