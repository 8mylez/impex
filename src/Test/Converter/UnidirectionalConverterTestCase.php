<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class UnidirectionalConverterTestCase extends ConverterTestCase
{
    abstract public static function convertProvider(): array;

    abstract protected function instantiateConverter(array $params = []): UnidirectionalConverter;

    #[DataProvider('convertProvider')]
    public function testConvert(mixed $input, mixed $expectedResult, ?bool $strict = true, ?string $exception = null, array $constructorParams = [])
    {
        $converter = $this->instantiateConverter($constructorParams);
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        if ($exception !== null) {
            $this->expectException($exception);
            $converter->convert($input, $context);

            return;
        }

        $result = $converter->convert($input, $context);

        if (boolval($strict) === true) {
            $this->assertSame($result, $expectedResult);
        } else {
            $this->assertEquals($result, $expectedResult);
        }
    }
}
