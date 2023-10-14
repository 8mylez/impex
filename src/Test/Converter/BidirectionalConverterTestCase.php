<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class BidirectionalConverterTestCase extends ConverterTestCase
{
    public static function normalizeProvider(): array
    {
        return static::createDataFromFile('normalize.json');
    }

    public static function denormalizeProvider(): array
    {
        return static::createDataFromFile('denormalize.json');
    }

    abstract protected function instantiateConverter(array $params = []): BidirectionalConverter;

    #[DataProvider('normalizeProvider')]
    public function testNormalize(mixed $input, mixed $expected, ?bool $strict = true, ?string $exception = null, array $constructorParams = []): void
    {
        $converter = $this->instantiateConverter($constructorParams);
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        if ($exception !== null) {
            $this->expectException($exception);
            $converter->normalize($input, $context);

            return;
        }

        $result = $converter->normalize($input, $context);

        if (boolval($strict) === true) {
            $this->assertSame($expected, $result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    #[DataProvider('denormalizeProvider')]
    public function testDenormalize(mixed $input, mixed $expected, ?bool $strict = true, ?string $exception = null, array $constructorParams = []): void
    {
        $converter = $this->instantiateConverter($constructorParams);
        $context = $this->createConversionContext(ConversionContext::DENORMALIZATION);

        if ($exception !== null) {
            $this->expectException($exception);
            $converter->denormalize($input, $context);

            return;
        }

        $result = $converter->denormalize($input, $context);

        if (boolval($strict) === true) {
            $this->assertSame($expected, $result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }
}
