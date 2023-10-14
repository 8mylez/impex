<?php

namespace Dustin\ImpEx\Test\Converter\Filter;

use Dustin\ImpEx\Serializer\Converter\ArrayList\ArrayConverter;
use Dustin\ImpEx\Serializer\Converter\ArrayList\Filter;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Test\Converter\CreateContextTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    use CreateContextTrait;

    #[DataProvider('filterProvider')]
    public function testFilter($input, $expectedResult, ?callable $callback = null, array $flags = [], ?string $exception = null)
    {
        $converter = new Filter($callback, ...$flags);
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        if ($exception !== null) {
            $this->expectException($exception);
            $converter->convert($input, $context);

            return;
        }

        $result = $converter->convert($input, $context);

        $this->assertEquals($expectedResult, $result);
    }

    public static function filterProvider()
    {
        return [
            [
                'input' => [null, 123],
                'expected' => [1 => 123],
            ], [
                'input' => [null, 123],
                'expected' => [123],
                'callback' => null,
                'flags' => [ArrayConverter::REINDEX],
            ], [
                'input' => [' '],
                'expected' => [],
                'callback' => function ($value) {
                    return !empty(trim($value));
                },
            ], [
                'input' => null,
                'expected' => null,
                'callback' => null,
                'flags' => [AttributeConverter::SKIP_NULL],
            ], [
                'input' => 123,
                'expected' => [123],
            ], [
                'input' => 123,
                'expected' => null,
                'callback' => null,
                'flags' => [AttributeConverter::STRICT],
                'exception' => InvalidTypeException::class,
            ],
        ];
    }
}
