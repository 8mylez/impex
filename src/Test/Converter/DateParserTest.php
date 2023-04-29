<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\DateTime\DateParser;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\DateConversionException;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;

class DateParserTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(?string $format = null, array $flags = []): UnidirectionalConverter
    {
        return new DateParser($format, ...$flags);
    }

    protected function strict(): bool
    {
        return false;
    }

    public function conversionProvider(): array
    {
        return [
            [
                '2023-11-01',
                date_create('2023-11-01'),
            ], [
                null,
                null,
                null,
                [null, [AttributeConverter::SKIP_NULL]],
            ], [
                [],
                null,
                InvalidTypeException::class,
                [null, [AttributeConverter::STRICT]],
            ], [
                123,
                null,
                DateConversionException::class,
            ], [
                '11.2021.19',
                date_create_from_format('Y-m-d', '2021-11-19'),
                null,
                ['m.Y.d'],
            ], [
                [],
                null,
                StringConversionException::class,
            ],
        ];
    }
}
