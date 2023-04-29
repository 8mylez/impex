<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Formatter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\NumericConversionException;

class FormatterTest extends UnidirectionalConverterTestCase
{
    protected function strict(): bool
    {
        return true;
    }

    protected function instantiateConverter(string $decimalSeparator = '.', string $thousandsSeparator = ',', int $decimals = 3, array $flags = []): UnidirectionalConverter
    {
        return new Formatter($decimalSeparator, $thousandsSeparator, $decimals, ...$flags);
    }

    public function conversionProvider(): array
    {
        return [
            [
                123.45,
                '123.450',
            ], [
                123456.7800,
                '123.456,78',
                null,
                [',', '.', 2],
            ], [
                null,
                null,
                null,
                [',', '.', 2, [AttributeConverter::SKIP_NULL]],
            ], [
                '123',
                null,
                InvalidTypeException::class,
                [',', '.', 2, [AttributeConverter::STRICT]],
            ], [
                '123',
                '123.000',
                null,
            ], [
                [],
                null,
                NumericConversionException::class,
            ],
        ];
    }
}
