<?php

namespace Dustin\ImpEx\Serializer\Exception;

class UnknownErrorException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_UNKNOWN_ERROR';

    public function __construct(
        string $attributePath,
        array $data,
        string $message,
        array $parameters,
        private ?string $errorCode = null
    ) {
        parent::__construct($attributePath, $data, $message, $parameters);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode ?? self::ERROR_CODE;
    }
}
