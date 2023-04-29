<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ListConverter;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Bool\BoolConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class ListConverterTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, AttributeConverter $converter, array $flags = [], ?string $exception = null)
    {
        $listConverter = new ListConverter($converter, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $listConverter->normalize($input, new Encapsulation(), '', '');

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, AttributeConverter $converter, array $flags = [], ?string $exception = null)
    {
        $listConverter = new ListConverter($converter, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $listConverter->denormalize($input, new Encapsulation(), '', '', []);

        $this->assertEquals($result, $expectedResult);
    }

    public function normalizeProvider()
    {
        return [
            [
                [[1, 2, 3]],
                [[[1], [2], [3]]],
                new Chunker(1),
            ], [
                null,
                null,
                new BoolConverter(),
                [AttributeConverter::SKIP_NULL],
            ], [
                '0',
                [false],
                new BoolConverter(),
            ], [
                '0',
                null,
                new BoolConverter(),
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                ['Hello world'],
                null,
                new Chunker(2, false, AttributeConverter::STRICT),
                [],
                AttributeConversionExceptionStack::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                [[[1], [2], [3]]],
                [[1, 2, 3]],
                new Chunker(1),
            ], [
                null,
                null,
                new BoolConverter(),
                [AttributeConverter::SKIP_NULL],
            ], [
                '0',
                [false],
                new BoolConverter(),
            ], [
                '0',
                null,
                new BoolConverter(),
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                [['Hello world']],
                null,
                new Chunker(1, false, AttributeConverter::STRICT),
                [],
                AttributeConversionExceptionStack::class,
            ],
        ];
    }
}
