<?php

namespace Dustin\ImpEx\Serializer\Exception;

class SerializationConversionException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_SERIALIZATION_CONVERSION_ERROR';

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
