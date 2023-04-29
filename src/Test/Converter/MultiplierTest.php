<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Multiplier;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\NumericConversionException;
use Dustin\ImpEx\Serializer\Exception\ZeroDivisionException;
use PHPUnit\Framework\TestCase;

class MultiplierTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, $factor, array $flags = [], ?string $exception = null)
    {
        $converter = new Multiplier($factor, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, $factor, array $flags = [], ?string $exception = null)
    {
        $converter = new Multiplier($factor, ...$flags);

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
                15,
                3,
            ], [
                10,
                5.0,
                0.5,
            ], [
                null,
                null,
                3,
                [AttributeConverter::SKIP_NULL],
            ], [
                '5',
                15,
                3,
            ], [
                '5',
                null,
                0,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                [],
                null,
                0,
                [],
                NumericConversionException::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                15,
                5,
                3,
            ], [
                10,
                20.0,
                0.5,
            ], [
                null,
                null,
                0,
                [AttributeConverter::SKIP_NULL],
            ], [
                '15',
                5,
                3,
            ], [
                '15',
                null,
                0,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                [],
                null,
                0,
                [],
                NumericConversionException::class,
            ], [
                15,
                null,
                0.0,
                [],
                ZeroDivisionException::class,
            ],
        ];
    }
}
