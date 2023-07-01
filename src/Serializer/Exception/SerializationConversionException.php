<?php

namespace Dustin\ImpEx\Serializer\Exception;

class SerializationConversionException extends AttributeConversionException
{
    public const ERROR_CODE_CIRCULAR_REFERENCE = 'IMPEX_SERIALIZATION_CONVERSION_CIRCULAR_REFERENCE_ERROR';

    public const ERROR_CODE_EXTRA_ATTRIBUTES = 'IMPEX_SERIALIZATION_CONVERSION_EXTRA_ATTRIBUTES_ERROR';

    public const ERROR_CODE_NOT_NORMALIZABLE_VALUE = 'IMPEX_SERIALIZATION_CONVERSION_NOT_NORMALIZABLE_VALUE_ERROR';

    private $errorCode = 'IMPEX_SERIALIZATION_CONVERSION_ERROR';

    public static function circularReference(string $attributePath, array $data): self
    {
        $exception = new self(
            $attributePath,
            $data,
            'Circular reference was detected',
            []
        );

        $exception->errorCode = self::ERROR_CODE_CIRCULAR_REFERENCE;

        return $exception;
    }

    public static function extraAttributes(string $attributePath, array $data, array $extraAttributes): self
    {
        $exception = new self(
            $attributePath,
            $data,
            'The attributes {{ extraAttributes }} are not allowed.',
            ['extraAttributes' => implode(', ', $extraAttributes)]
        );

        $exception->errorCode = self::ERROR_CODE_EXTRA_ATTRIBUTES;

        return $exception;
    }

    public static function notNormalizableValue(string $attributePath, array $data, string $message): self
    {
        $exception = new self(
            $attributePath,
            $data,
            $message,
            []
        );

        $exception->errorCode = self::ERROR_CODE_NOT_NORMALIZABLE_VALUE;

        return $exception;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
