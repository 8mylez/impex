<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;

class NotAccessableException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS_NOT_ACCESSABLE';

    public function __construct(private string $type)
    {
        parent::__construct(
            'Value of type {{ type }} is not accessable.',
            ['type' => $type]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
