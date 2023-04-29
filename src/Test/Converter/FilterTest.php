<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\Filter;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testFilter($input, $expectedResult, ?callable $callback = null, array $flags = [], ?string $exception = null)
    {
        $converter = new Filter($callback, ...$flags);

        if ($exception !== null) {
            $this->expectException($exception);
        }

        $result = $converter->convert($input, new Encapsulation(), '', '', []);

        $this->assertEquals($result, $expectedResult);
    }

    public function filterProvider()
    {
        return [
            [
                [null, 123],
                [1 => 123],
            ], [
                [null, 123],
                [123],
                null,
                [ArrayConverter::REINDEX],
            ], [
                [' '],
                [],
                function ($value) {
                    return !empty(trim($value));
                },
            ], [
                null,
                null,
                null,
                [AttributeConverter::SKIP_NULL],
            ], [
                123,
                [123],
            ], [
                123,
                null,
                null,
                [AttributeConverter::STRICT],
                InvalidTypeException::class,
            ],
        ];
    }
}
