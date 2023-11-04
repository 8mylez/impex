<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class InvalidTypeException extends AttributeConversionException
{
    public const INVALID_TYPE_ERROR = 'IMPEX_CONVERSION__INVALID_TYPE_ERROR';

    public const INVALID_ARRAY_KEY_ERROR = 'IMPEX_CONVERSION__INVALID_ARRAY_KEY_ERROR';

    public static function invalidType(ConversionContext $context, string $expectedType, mixed $value): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'Expected value to be {{ expectedType }}. Got {{ actualType }}.',
            ['expectedType' => $expectedType, 'actualType' => Type::getDebugType($value)],
            self::INVALID_TYPE_ERROR
        );
    }

    public static function invalidArrayKey(ConversionContext $context, mixed $value): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'Value of type {{ type }} cannot be used as array key.',
            ['type' => Type::getDebugType($value)],
            self::INVALID_ARRAY_KEY_ERROR
        );
    }
}
