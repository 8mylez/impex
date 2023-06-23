<?php

namespace Dustin\ImpEx\Serializer\Exception;

class InvalidArrayException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_INVALID_ARRAY_ERROR';

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
