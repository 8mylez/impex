<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Adder;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\NumericConversionException;
use PHPUnit\Framework\TestCase;

class AdderTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, $summand, array $flags = [], ?string $exception = null)
    {
        $converter = new Adder($summand, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, $summand, array $flags = [], ?string $exception = null)
    {
        $converter = new Adder($summand, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->denormalize($input, new Encapsulation(), '', '', []);

        $this->assertSame($result, $expectedResult);
    }

    public function normalizeProvider()
    {
        return [
            [
                5,
                8,
                3,
            ], [
                5.1,
                5.5,
                0.4,
            ], [
                5.0,
                8.0,
                3,
            ], [
                null,
                null,
                0,
                [AttributeConverter::SKIP_NULL],
            ], [
                '5',
                8,
                3,
            ], [
                '5.1',
                8.1,
                3,
            ], [
                '5.1',
                null,
                3,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                5.1,
                8.1,
                3,
                [AttributeConverter::STRICT],
            ], [
                [],
                null,
                3,
                [],
                NumericConversionException::class,
            ], [
                8,
                5,
                -3,
            ], [
                '-8.1',
                -5.1,
                3,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                8,
                5,
                3,
            ], [
                8.1,
                5.1,
                3,
            ], [
                8.0,
                5.0,
                3,
            ], [
                null,
                null,
                0,
                [AttributeConverter::SKIP_NULL],
            ], [
                '8',
                5,
                3,
            ], [
                '8.1',
                5.1,
                3,
            ], [
                '8.1',
                5.1,
                3,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                8.1,
                5.1,
                3,
                [AttributeConverter::STRICT],
            ], [
                [],
                null,
                0,
                [],
                NumericConversionException::class,
            ], [
                '-5.1',
                -8.1,
                3,
            ],
        ];
    }
}
