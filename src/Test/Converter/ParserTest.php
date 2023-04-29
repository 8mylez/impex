<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Numeric\Parser;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;

class ParserTest extends UnidirectionalConverterTestCase
{
    protected function strict(): bool
    {
        return true;
    }

    protected function instantiateConverter(string $decimalSeparator = '.', string $thousandsSeparator = ',', array $flags = []): UnidirectionalConverter
    {
        return new Parser($decimalSeparator, $thousandsSeparator, ...$flags);
    }

    public function conversionProvider(): array
    {
        return [
            [
                '123',
                123,
            ], [
                '123.4',
                123.4,
            ], [
                '123.456,7',
                123456.7,
                null,
                [',', '.'],
            ], [
                null,
                null,
                null,
                ['.', ',', [AttributeConverter::SKIP_NULL]],
            ], [
                123,
                null,
                InvalidTypeException::class,
                ['.', ',', [AttributeConverter::STRICT]],
            ], [
                [],
                null,
                StringConversionException::class,
            ], [
                'KKK123.4',
                null,
                null,
            ], [
                'KKKK123.4',
                123.4,
                null,
                ['.', ',', [Parser::IGNORE_LEADING_CHARACTERS]],
            ], [
                'KKK123KK456',
                123,
                null,
                ['.', ',', [Parser::IGNORE_LEADING_CHARACTERS]],
            ], [
                'KKK123KK456',
                123456,
                null,
                ['.', ',', [Parser::IGNORE_ALL_CHARACTERS]],
            ], [
                '-123.4',
                -123.4,
            ], [
                'KK-123.4KK45',
                -123.4,
                null,
                ['.', ',', [Parser::IGNORE_LEADING_CHARACTERS]],
            ], [
                '+-123',
                null,
            ], [
                '+-123.4',
                -123.4,
                null,
                ['.', ',', [Parser::IGNORE_LEADING_CHARACTERS]],
            ], [
                'KKK+-123,45,456.4KK',
                -123,
                null,
                ['.', ',', [Parser::IGNORE_ALL_CHARACTERS]],
            ], [
                'KK123.456.789',
                123.456,
                null,
                ['.', ',', [Parser::IGNORE_LEADING_CHARACTERS]],
            ], [
                'KK123',
                0,
                null,
                ['.', ',', [Parser::EMPTY_TO_ZERO]],
            ], [
                '  123 45 ',
                123.45,
                null,
                [' ', ','],
            ], [
                '123456,7',
                123456.7,
                null,
                [',', ''],
            ],
        ];
    }
}
