<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Bool\BoolConverter;
use PHPUnit\Framework\TestCase;

class BoolConverterTest extends TestCase
{
    /**
     * @dataProvider boolConverterProvider
     */
    public function testBoolConverter($input, $expectedResult, array $flags = [])
    {
        $converter = new BoolConverter(...$flags);

        $result = $converter->convert($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    public function boolConverterProvider()
    {
        return [
            [
                '0',
                false,
            ], [
                0,
                false,
            ], [
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                'false',
                true,
            ], [
                1,
                true,
            ], [
                '1',
                true,
            ],
        ];
    }
}
