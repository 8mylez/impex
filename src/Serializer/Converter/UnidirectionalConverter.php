<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;

/**
 * Converts an attribute value in only one direction.
 *
 * An UnidirectionalConverter is used for conversion operations which cannot be reversed (e.g. creating the md5 hash of a string).
 * This converter will execute the same conversion operation in both directions (normalization and denormalization).
 */
abstract class UnidirectionalConverter extends AttributeConverter
{
    /**
     * @param mixed                  $value          The value to convert
     * @param EncapsulationInterface $object         The encapsulation object to be normalized by a normalizer
     * @param string                 $path           The full path of the current attribute in relation to the object to be normalized
     * @param string                 $attributeName  The name of the attribute or object property
     * @param array|null             $normalizedData The origin data only available on denormalization
     */
    abstract public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $normalizedData = null);

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        return $this->convert($value, $object, $path, $attributeName);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        return $this->convert($value, $object, $path, $attributeName, $normalizedData);
    }
}
