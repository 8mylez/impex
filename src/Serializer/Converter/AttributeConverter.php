<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\NumericConversionException;
use Dustin\ImpEx\Serializer\Exception\StringConversionException;
use Dustin\ImpEx\Util\Type;

/**
 * Converter base class to convert an attribute value.
 *
 * Attribute values can be converted in both directions (normalization and denormalization).
 * Flags can be set to change conversion behavior and influence error handling.
 */
abstract class AttributeConverter
{
    public const SKIP_NULL = 'skip_null';

    public const STRICT = 'strict';

    public const REVERSE = 'reverse';

    public const REINDEX = 'reindex';

    private $flags = [];

    /**
     * @param string ...$flags An optional list of flags to affect conversion behavior
     */
    public function __construct(string ...$flags)
    {
        foreach ($flags as $flag) {
            $this->flags[$flag] = $flag;
        }
    }

    abstract public function normalize(mixed $value, ConversionContext $context): mixed;

    abstract public function denormalize(mixed $value, ConversionContext $context): mixed;

    /**
     * Checks wether a given flag is set or not.
     */
    protected function hasFlags(string ...$flags): bool
    {
        foreach ($flags as $flag) {
            if (!isset($this->flags[$flag])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns wether at least one of the given flags is set.
     */
    protected function hasOneOfFlags(string ...$flags): bool
    {
        foreach ($flags as $flag) {
            if (isset($this->flags[$flag])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates of a given value is an expected type.
     *
     * @throws InvalidTypeException Thrown if the value is not of the expected type
     */
    protected function validateType(mixed $value, string $expectedType, ConversionContext $context): void
    {
        if (!Type::is($value, $expectedType)) {
            throw new InvalidTypeException($context->getPath(), $context->getRootData(), $expectedType, $value);
        }
    }

    /**
     * Validates if a value can be converted into a string.
     *
     * @throws StringConversionException Thrown if the given value cannot be converted to a string (e.g. arrays or objects)
     */
    protected function validateStringConvertable(mixed $value, ConversionContext $context): void
    {
        if (!Type::isStringConvertable(Type::getType($value))) {
            throw new StringConversionException($value, $context->getPath(), $context->getRootData());
        }
    }

    /**
     * Validates if a value can be converted into an integer or float.
     *
     * @throws NumericConversionException Thrown if the given value cannot be converted into a numeric value
     */
    protected function validateNumericConvertable(mixed $value, ConversionContext $context): void
    {
        if (!Type::isNumericConvertable(Type::getType($value))) {
            throw new NumericConversionException($value, $context->getPath(), $context->getRootData());
        }
    }
}
