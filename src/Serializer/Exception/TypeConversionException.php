<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class TypeConversionException extends AttributeConversionException
{
    public const NUMERIC_CONVERSION_ERROR = 'IMPEX_CONVERSION__NUMERIC_CONVERSION_ERROR';

    public const STRING_CONVERSION_ERROR = 'IMPEX_CONVERSION__STRING_CONVERSION_ERROR';

    public static function numeric(mixed $value, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'Value of type {{ type }} cannot be converted to int or float.',
            ['type' => Type::getDebugType($value)],
            self::NUMERIC_CONVERSION_ERROR
        );
    }

    public static function string(mixed $value, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'Value of type {{ type }} cannot be converted to string.',
            ['type' => Type::getDebugType($value)],
            self::STRING_CONVERSION_ERROR
        );
    }
}
