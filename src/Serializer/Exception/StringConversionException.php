<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class StringConversionException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_STRING_CONVERSION_ERROR';

    public function __construct($value, string $path, array $data)
    {
        parent::__construct(
            $path, $data,
            '{{ value }} cannot be converted to string',
            [
                'value' => Type::getDebugType($value),
            ]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
