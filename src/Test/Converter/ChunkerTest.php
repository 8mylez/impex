<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class ChunkerTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(int $chunkSize, $input, $expectedResult, array $flags = [], bool $preserveKeys = false, ?string $exception = null)
    {
        $converter = new Chunker($chunkSize, $preserveKeys, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->normalize($input, new Encapsulation(), '', '');

        $this->assertSame($result, $expectedResult);
    }

    /**
     * @dataProvider denormalizeProvider
     */
    public function testDenormalize(int $chunkSize, $input, $expectedResult, array $flags = [], bool $preserveKeys = false, ?string $exception = null)
    {
        $converter = new Chunker($chunkSize, $preserveKeys, ...$flags);

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
                2,
                [1, 2, 3, 4],
                [[1, 2], [3, 4]],
            ], [
                2,
                1,
                [[1]],
            ], [
                1,
                ['foo' => 'foo', 'bar' => 'bar'],
                [['foo' => 'foo'], ['bar' => 'bar']],
                [],
                true,
            ], [
                2,
                1,
                null,
                [AttributeConverter::STRICT],
                false,
                InvalidTypeException::class,
            ], [
                2,
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                2,
                [[1, 2], [3, 4]],
                [1, 2, 3, 4],
                [AttributeConverter::REVERSE],
            ], [
                2,
                [1, 2, 3, 4],
                null,
                [AttributeConverter::REVERSE],
                false,
                AttributeConversionExceptionStack::class,
            ], [
                2,
                [[1, 2], [1, 2, 3]],
                null,
                [Chunker::REVERSE, Chunker::STRICT_CHUNK_SIZE],
                false,
                AttributeConversionExceptionStack::class,
            ],
        ];
    }

    public function denormalizeProvider()
    {
        return [
            [
                2,
                [[1, 2], [3, 4]],
                [1, 2, 3, 4],
            ], [
                2,
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                2,
                123,
                null,
                [AttributeConverter::STRICT],
                false,
                InvalidTypeException::class,
            ], [
                2,
                [1, 2, 3, 4],
                [[1, 2], [3, 4]],
                [AttributeConverter::REVERSE],
            ], [
                2,
                [[1, 2], [3, 4, 5]],
                [1, 2, 3, 4, 5],
            ], [
                2,
                [[1, 2], [3, 4]],
                [1, 2, 3, 4],
                [Chunker::STRICT_CHUNK_SIZE],
            ], [
                2,
                [[1, 2], [3]],
                [1, 2, 3],
                [Chunker::STRICT_CHUNK_SIZE],
            ], [
                2,
                [[1, 2], [3, 4, 5]],
                null,
                [Chunker::STRICT_CHUNK_SIZE],
                false,
                AttributeConversionExceptionStack::class,
            ],
        ];
    }
}
