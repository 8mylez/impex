<?php

namespace Dustin\ImpEx\Serializer\Exception;

class ZeroDivisionException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_ZERO_DIVISION_ERROR';

    public function __construct(string $path, array $data)
    {
        parent::__construct(
            $path, $data,
            'Division by zero was detected',
            []
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
