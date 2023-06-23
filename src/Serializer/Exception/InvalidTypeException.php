<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class InvalidTypeException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_INVALID_TYPE_ERROR';

    public function __construct(string $path, array $data, string $expectedType, $value)
    {
        parent::__construct(
            $path, $data,
            'Expected value to be {{ expectedValue }}. Got {{ value }}',
            [
                'expectedValue' => $expectedType,
                'value' => Type::getDebugType($value),
            ]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
