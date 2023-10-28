<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class TypeConversionException extends AttributeConversionException
{
    public const NUMERIC_CONVERSION_ERROR = 'IMPEX_CONVERSION__NUMERIC_CONVERSION_ERROR';

    public const STRING_CONVERSION_ERROR = 'IMPEX_CONVERSION__STRING_CONVERSION_ERROR';

    public static function numeric(string $path, array $data, mixed $value): self
    {
        return new self(
            $path, $data,
            'Value of type {{ type }} cannot be converted to int or float.',
            ['type' => Type::getDebugType($value)],
            self::NUMERIC_CONVERSION_ERROR
        );
    }

    public static function string(string $path, array $data, mixed $value): self
    {
        return new self(
            $path, $data,
            'Value of type {{ type }} cannot be converted to string.',
            ['type' => Type::getDebugType($value)],
            self::STRING_CONVERSION_ERROR
        );
    }
}
