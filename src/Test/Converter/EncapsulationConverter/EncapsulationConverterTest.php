<?php

namespace Dustin\ImpEx\Test\Converter\EncapsulationConverter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\NestedEncapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\EncapsulationConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Test\Converter\CreateContextTrait;
use PHPUnit\Framework\TestCase;

class EncapsulationConverterTest extends TestCase
{
    use CreateContextTrait;

    public function testEncapsulationConverter()
    {
        $converter = new EncapsulationConverter();
        $encapsulation = new Encapsulation(['foo' => 'foo', 'bar' => 'bar']);
        $value = ['foo' => 'foo', 'bar' => 'bar'];
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        $denormalized = $converter->normalize($encapsulation, $context);
        $this->assertEquals($value, $denormalized);

        $normalized = $converter->denormalize($value, $context);
        $this->assertEquals($encapsulation, $normalized);

        $emptyEncapsulation = $converter->denormalize(null, $context);
        $this->assertEquals(new Encapsulation(), $emptyEncapsulation);

        $this->expectException(InvalidTypeException::class);
        $converter->normalize(null, $context);
    }

    public function testWithGivenClass()
    {
        $converter = new EncapsulationConverter(NestedEncapsulation::class);
        $encapsulation = new NestedEncapsulation(['foo' => 'foo', 'bar' => 'bar']);
        $value = ['foo' => 'foo', 'bar' => 'bar'];
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        $denormalized = $converter->normalize($encapsulation, $context);
        $this->assertEquals($denormalized, $value);

        $normalized = $converter->denormalize($value, $context);
        $this->assertEquals($normalized, $encapsulation);
    }

    public function testSkipNull()
    {
        $converter = new EncapsulationConverter(Encapsulation::class, AttributeConverter::SKIP_NULL);
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        $denormalized = $converter->normalize(null, $context);
        $this->assertNull($denormalized);

        $normalized = $converter->denormalize(null, $context);
        $this->assertNull($normalized);
    }

    public function testStrict()
    {
        $converter = new EncapsulationConverter(Encapsulation::class, AttributeConverter::STRICT);
        $context = $this->createConversionContext(ConversionContext::NORMALIZATION);

        $this->expectException(InvalidTypeException::class);
        $converter->denormalize(null, $context);
    }
}
