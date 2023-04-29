<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\NestedEncapsulation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\EncapsulationConverter;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;

class EncapsulationConverterTest extends TestCase
{
    public function testEncapsulationConverter()
    {
        $converter = new EncapsulationConverter();
        $encapsulation = new Encapsulation(['foo' => 'foo', 'bar' => 'bar']);
        $value = ['foo' => 'foo', 'bar' => 'bar'];

        $denormalized = $converter->normalize($encapsulation, new Encapsulation(), '', '');
        $this->assertEquals($denormalized, $value);

        $normalized = $converter->denormalize($value, new Encapsulation(), '', '', []);
        $this->assertEquals($normalized, $encapsulation);

        $emptyEncapsulation = $converter->denormalize(null, new Encapsulation(), '', '', []);
        $this->assertEquals($emptyEncapsulation, new Encapsulation());

        $this->expectException(InvalidTypeException::class);
        $converter->normalize(null, new Encapsulation(), '', '');
    }

    public function testWithGivenClass()
    {
        $converter = new EncapsulationConverter(NestedEncapsulation::class);
        $encapsulation = new NestedEncapsulation(['foo' => 'foo', 'bar' => 'bar']);
        $value = ['foo' => 'foo', 'bar' => 'bar'];

        $denormalized = $converter->normalize($encapsulation, new Encapsulation(), '', '');
        $this->assertEquals($denormalized, $value);

        $normalized = $converter->denormalize($value, new Encapsulation(), '', '', []);
        $this->assertEquals($normalized, $encapsulation);
    }

    public function testSkipNull()
    {
        $converter = new EncapsulationConverter(Encapsulation::class, AttributeConverter::SKIP_NULL);

        $denormalized = $converter->normalize(null, new Encapsulation(), '', '');
        $this->assertNull($denormalized);

        $normalized = $converter->denormalize(null, new Encapsulation(), '', '', []);
        $this->assertNull($normalized);
    }

    public function testStrict()
    {
        $converter = new EncapsulationConverter(Encapsulation::class, AttributeConverter::STRICT);

        $this->expectException(InvalidTypeException::class);
        $converter->denormalize(null, new Encapsulation(), '', '', []);
    }
}
