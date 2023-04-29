<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConcatConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ConverterMapping;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\Bool\BoolConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class ConverterMappingTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($input, $expectedResult, array $mapping, array $flags = [], ?string $exception = null)
    {
        $converter = new ConverterMapping($mapping, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize($input, $expectedResult, array $mapping, array $flags = [], ?string $exception = null)
    {
        $converter = new ConverterMapping($mapping, ...$flags);

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
                ['foo' => ['hello', 'world'], 'bar' => '0'],
                ['foo' => 'hello world', 'bar' => false],
                ['foo' => new ConcatConverter(' '), 'bar' => new BoolConverter()],
            ], [
                null,
                null,
                [],
                [AttributeConverter::SKIP_NULL],
            ], [
                'Hello world',
                [0 => 'Hello world'],
                [],
            ], [
                'Hello world',
                [0 => ['Hello', 'world']],
                [0 => new ConcatConverter(' ', AttributeConverter::REVERSE)],
            ], [
                'Hello world',
                null,
                [],
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ], [
                ['foo' => ' foo ', 'bar' => '0'],
                ['foo' => ' foo ', 'bar' => false],
                ['bar' => new BoolConverter()],
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                ['foo' => 'Hello world', 'bar' => 'bar'],
                ['foo' => ['Hello', 'world'], 'bar' => 'bar'],
                ['foo' => new ConcatConverter(' ')],
            ], [
                'Hello world',
                [0 => 'Hello world'],
                [],
            ], [
                null,
                null,
                [],
                [AttributeConverter::SKIP_NULL],
            ], [
                'Hello world',
                [0 => ['Hello', 'world']],
                [0 => new ConcatConverter(' ')],
            ], [
                'Hello world',
                null,
                [],
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ],
        ];
    }
}
