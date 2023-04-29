<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateTimeConverter;
use Dustin\ImpEx\Serializer\Exception\DateConversionException;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;
use PHPUnit\Framework\TestCase;

class DateTimeConverterTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, string $format, array $flags = [], ?string $exception = null)
    {
        $converter = new DateTimeConverter($format, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, string $format, array $flags = [], ?string $exception = null)
    {
        $converter = new DateTimeConverter($format, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->denormalize($input, new Encapsulation(), '', '', []);

        $this->assertEquals($result, $expectedResult);
    }

    public function normalizeProvider()
    {
        return [
            [
                date_create('2023-05-04'),
                '04.05.2023',
                'd.m.Y',
            ], [
                null,
                null,
                '',
                [AttributeConverter::SKIP_NULL],
            ], [
                'dateTime',
                null,
                '',
                [],
                InvalidTypeException::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                '04.05.2023 12:10:23',
                date_create('2023-05-04 12:10:23'),
                'd.m.Y H:i:s',
            ], [
                null,
                null,
                '',
                [AttributeConverter::SKIP_NULL],
            ], [
                123,
                null,
                '',
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                123,
                null,
                '',
                [],
                DateConversionException::class,
            ], [
                [],
                null,
                '',
                [],
                StringConversionException::class,
            ],
        ];
    }
}
