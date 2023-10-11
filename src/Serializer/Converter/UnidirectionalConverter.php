<?php

namespace Dustin\ImpEx\Serializer\Converter;

/**
 * Converts an attribute value in only one direction.
 *
 * An UnidirectionalConverter is used for conversion operations which cannot be reversed (e.g. creating the md5 hash of a string).
 * This converter will execute the same conversion operation in both directions (normalization and denormalization).
 */
abstract class UnidirectionalConverter extends AttributeConverter
{
    /**
     * @param mixed $value The value to convert
     */
    abstract public function convert(mixed $value, ConversionContext $context): mixed;

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        return $this->convert($value, $context);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        return $this->convert($value, $context);
    }
}
