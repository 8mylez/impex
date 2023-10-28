<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\Util\Type;

class InvalidDataException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS__INVALID_DATA_ERROR';

    public static function notMergable(mixed $data): self
    {
        return new self(
            'Value of type {{ type }} is not mergable.',
            ['type' => Type::getDebugType($data)]
        );
    }

    public static function keyNotNumeric(string $key): self
    {
        return new self(
            'Key {{ key }} is not numeric.',
            ['key' => $key]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
