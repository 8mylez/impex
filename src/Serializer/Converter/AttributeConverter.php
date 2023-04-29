<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
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

    /**
     * Returns a list of all flags which can be applied to a converter instance.
     */
    public static function getAvailableFlags(): array
    {
        return [];
    }

    /**
     * @param mixed                  $value         The value to convert
     * @param EncapsulationInterface $object        The encapsulation object to be normalized by a normalizer
     * @param string                 $path          The full path of the current attribute in relation to the object to be normalized
     * @param string                 $attributeName The name of the attribute or object property
     *
     * @return mixed
     */
    abstract public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName);

    /**
     * @param mixed                  $value          The value to converter
     * @param EncapsulationInterface $object         The encapsulation object to be normalized by a normalizer
     * @param string                 $path           The full path of the attribute in relation to the obejct to be normalized
     * @param string                 $attributeName  The name of the attribute or object property
     * @param array                  $normalizedData The data to be denormalized into an object
     *
     * @return mixed
     */
    abstract public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData);

    /**
     * Checks wether a given flag is set or not.
     */
    protected function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]);
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
     * @param mixed  $value        The value to validate
     * @param string $expectedType Must be one of the type constants from {@see} Type
     * @param string $path         The path of the attribute on relation to the object or normalized data
     * @param array  $data         The data to be denormalized or normalized
     *
     * @throws InvalidTypeException Thrown if the value is not of the expected type
     */
    protected function validateType($value, string $expectedType, string $path, array $data)
    {
        if (!Type::is($value, $expectedType)) {
            throw new InvalidTypeException($path, $data, $expectedType, $value);
        }
    }

    /**
     * Validates if a value can be converted into a string.
     *
     * @param mixed  $value The value to validate
     * @param string $path  The path of the attribute on relation to the object or normalized data
     * @param array  $data  The data to be denormalized or normalized
     *
     * @throws StringConversionException Thrown if the given value cannot be converted to a string (e.g. arrays or objects)
     */
    protected function validateStringConvertable($value, string $path, array $data): void
    {
        if (!Type::isStringConvertable(Type::getType($value))) {
            throw new StringConversionException($value, $path, $data);
        }
    }

    /**
     * Validates if a value can be converted into an integer or float.
     *
     * @param mixed  $value The value to validate
     * @param string $path  The path of the attribute on relation to the object or normalized data
     * @param array  $data  The data to be denormalized or normalized
     *
     * @throws NumericConversionException Thrown if the given value cannot be converted into a numeric value
     */
    protected function validateNumericConvertable($value, string $path, array $data): void
    {
        if (!Type::isNumericConvertable(Type::getType($value))) {
            throw new NumericConversionException($value, $path, $data);
        }
    }
}
