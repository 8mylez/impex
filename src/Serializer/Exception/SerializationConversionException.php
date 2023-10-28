<?php

namespace Dustin\ImpEx\Serializer\Exception;

class SerializationConversionException extends AttributeConversionException
{
    public const CIRCULAR_REFERENCE_ERROR = 'IMPEX_CONVERSION__CIRCULAR_REFERENCE_ERROR';

    public const EXTRA_ATTRIBUTES_ERROR = 'IMPEX_CONVERSION__EXTRA_ATTRIBUTES_ERROR';

    public const NOT_NORMALIZABLE_VALUE_ERROR = 'IMPEX_CONVERSION___NOT_NORMALIZABLE_VALUE_ERROR';

    public static function circularReference(string $attributePath, array $data): self
    {
        return new self(
            $attributePath,
            $data,
            'Circular reference was detected',
            [],
            self::CIRCULAR_REFERENCE_ERROR
        );
    }

    public static function extraAttributes(string $attributePath, array $data, array $extraAttributes): self
    {
        return new self(
            $attributePath,
            $data,
            'The attributes {{ extraAttributes }} are not allowed.',
            ['extraAttributes' => implode(', ', $extraAttributes)],
            self::EXTRA_ATTRIBUTES_ERROR
        );
    }

    public static function notNormalizableValue(string $attributePath, array $data, string $message): self
    {
        return new self(
            $attributePath,
            $data,
            $message,
            [],
            self::NOT_NORMALIZABLE_VALUE_ERROR
        );
    }
}
