<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;

class AttributeConverterSequence extends BidirectionalConverter
{
    /**
     * @var array
     */
    private $converters = [];

    public function __construct(AttributeConverter ...$converters)
    {
        $this->converters = $converters;
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        foreach ($this->converters as $converter) {
            $value = $converter->normalize($value, $object, $path, $attributeName);
        }

        return $value;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        foreach ($this->converters as $converter) {
            $value = $converter->denormalize($value, $object, $path, $attributeName, $normalizedData);
        }

        return $value;
    }
}
