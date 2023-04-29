<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateFormatConverter;
use Dustin\ImpEx\Serializer\Exception\DateConversionException;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;
use PHPUnit\Framework\TestCase;

class DateFormatConverterTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, string $attributeFormat, string $rawFormat, array $flags = [], ?string $exception = null)
    {
        $converter = new DateFormatConverter($attributeFormat, $rawFormat, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, string $attributeFormat, string $rawFormat, array $flags = [], ?string $exception = null)
    {
        $converter = new DateFormatConverter($attributeFormat, $rawFormat, ...$flags);

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
                '2023-04-05',
                '05.04.2023',
                'Y-m-d',
                'd.m.Y',
            ], [
                null,
                null,
                '',
                '',
                [AttributeConverter::SKIP_NULL],
            ], [
                '',
                null,
                'Y-m-d',
                'd.m.Y',
                [],
                DateConversionException::class,
            ], [
                [],
                null,
                '',
                '',
                [],
                StringConversionException::class,
            ], [
                [],
                null,
                '',
                '',
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                '05.04.2023',
                '2023-04-05',
                'Y-m-d',
                'd.m.Y',
            ], [
                null,
                null,
                '',
                '',
                [AttributeConverter::SKIP_NULL],
            ], [
                '',
                null,
                'Y-m-d',
                'd.m.Y',
                [],
                DateConversionException::class,
            ], [
                [],
                null,
                '',
                '',
                [],
                StringConversionException::class,
            ], [
                [],
                null,
                '',
                '',
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ],
        ];
    }
}
