<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Serializer\Exception\TypeConversionException;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

trait ConversionTrait
{
    protected readonly array $flags;

    private function setFlags(string ...$flags): void
    {
        $f = [];

        foreach ($flags as $flag) {
            $f[$flag] = $flag;
        }

        $this->flags = $f;
    }

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

    protected function ensureType(mixed $value, string $type, ConversionContext $context): mixed
    {
        if (!$this->hasFlags(AttributeConverter::STRICT) && !Type::is($value, $type)) {
            switch ($type) {
                case Type::STRING:
                    $this->validateStringConvertable($value, $context);
                    $value = (string) $value;
                    break;
                case Type::NUMERIC:
                    $this->validateNumericConvertable($value, $context);
                    $value = $this->convertToNumeric($value);
                    break;
                case Type::ARRAY:
                    $value = ArrayUtil::ensure($value);
                    break;
            }
        }

        $this->validateType($value, $type, $context);

        return $value;
    }

    /**
     * Validates of a given value is an expected type.
     *
     * @throws InvalidTypeException Thrown if the value is not of the expected type
     */
    protected function validateType(mixed $value, string $expectedType, ConversionContext $context): void
    {
        if (!Type::is($value, $expectedType)) {
            throw InvalidTypeException::invalidType($expectedType, $value, $context);
        }
    }

    /**
     * Validates if a value can be converted into a string.
     *
     * @throws TypeConversionException Thrown if the given value cannot be converted to a string (e.g. arrays or objects)
     */
    protected function validateStringConvertable(mixed $value, ConversionContext $context): void
    {
        if (
            !Type::isStringConvertable(Type::getType($value)) &&
            !(is_object($value) && \method_exists($value, '__toString'))
        ) {
            throw TypeConversionException::string($value, $context);
        }
    }

    /**
     * Validates if a value can be converted into an integer or float.
     *
     * @throws TypeConversionException Thrown if the given value cannot be converted into a numeric value
     */
    protected function validateNumericConvertable(mixed $value, ConversionContext $context): void
    {
        if (!Type::isNumericConvertable(Type::getType($value))) {
            throw TypeConversionException::numeric($value, $context);
        }
    }

    /**
     * Converts a given value into integer or float.
     *
     * @param mixed $value
     */
    protected function convertToNumeric(string|null|int|float|bool $value): int|float
    {
        $value = floatval($value);

        if (floor($value) === $value) {
            $value = intval($value);
        }

        return $value;
    }
}
