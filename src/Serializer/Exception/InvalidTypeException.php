<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class InvalidTypeException extends AttributeConversionException
{
    public const INVALID_TYPE_ERROR = 'IMPEX_CONVERSION__INVALID_TYPE_ERROR';

    public static function invalidType(string $path, array $data, string $expectedType, mixed $value): self
    {
        return new self(
            $path, $data,
            'Expected value to be {{ expectedType }}. Got {{ actualType }}.',
            ['expectedType' => $expectedType, 'actualType' => Type::getDebugType($value)],
            self::INVALID_TYPE_ERROR
        );
    }
}
