<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;
use PHPUnit\Framework\TestCase;

class ConcatConverterTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, array $flags = [], ?string $exception = null)
    {
        $converter = new ConcatConverter(',', ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, array $flags = [], ?string $exception = null)
    {
        $converter = new ConcatConverter(',', ...$flags);

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
                ['Hello', 'world'],
                'Hello,world',
            ], [
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                'Hello world',
                'Hello world',
            ], [
                'Hello world',
                null,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                [[1], [2]],
                null,
                [],
                AttributeConversionExceptionStack::class,
            ], [
                'Hello,world',
                ['Hello', 'world'],
                [AttributeConverter::REVERSE],
            ], [
                ['Hello', 'world'],
                null,
                [AttributeConverter::REVERSE],
                StringConversionException::class,
            ], [
                123,
                ['123'],
                [AttributeConverter::REVERSE],
            ], [
                123,
                null,
                [AttributeConverter::REVERSE, AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                [123],
                null,
                [AttributeConverter::REVERSE],
                StringConversionException::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                'Hello,world',
                ['Hello', 'world'],
            ], [
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                'Hello world',
                ['Hello world'],
            ], [
                123,
                null,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                ['Hello world'],
                null,
                [],
                StringConversionException::class,
            ], [
                ['Hello', 'world'],
                'Hello,world',
                [AttributeConverter::REVERSE],
            ], [
                [['Hello'], 'world'],
                null,
                [AttributeConverter::REVERSE],
                AttributeConversionExceptionStack::class,
            ], [
                123,
                '123',
                [AttributeConverter::REVERSE],
            ], [
                123,
                null,
                [AttributeConverter::REVERSE, AttributeConverter::STRICT],
                InvalidTypeException::class,
            ],
        ];
    }
}
