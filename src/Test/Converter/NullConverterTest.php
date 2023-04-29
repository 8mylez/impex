<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\NullConverter;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class NullConverterTest extends UnidirectionalConverterTestCase
{
    protected function instantiateConverter(array $flags = []): UnidirectionalConverter
    {
        return new NullConverter(...$flags);
    }

    protected function strict(): bool
    {
        return true;
    }

    public function conversionProvider(): array
    {
        return [
            [
                'ABC',
                'ABC',
            ], [
                '',
                null,
            ], [
                '0',
                 null,
            ], [
                0,
                null,
            ], [
                123,
                123,
            ], [
                [],
                null,
            ], [
                ['foo'],
                 ['foo'],
            ], [
                null,
                null,
            ], [
                true,
                true,
            ], [
                false,
                null,
            ], [
                '',
                '',
                null,
                [[NullConverter::ALLOW_STRING]],
            ], [
                '0',
                '0',
                null,
                [[NullConverter::ALLOW_ZERO_STRING]],
            ], [
                0,
                0,
                null,
                [[NullConverter::ALLOW_NUMERIC]],
            ], [
                0.0,
                0.0,
                null,
                [[NullConverter::ALLOW_NUMERIC]],
            ], [
                [],
                [],
                null,
                [[NullConverter::ALLOW_ARRAY]],
            ], [
                false,
                false,
                null,
                [[NullConverter::ALLOW_BOOL]],
            ],
        ];
    }
}
