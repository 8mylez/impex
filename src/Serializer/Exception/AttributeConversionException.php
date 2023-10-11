<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCodeException;

abstract class AttributeConversionException extends ErrorCodeException
{
    public function __construct(
        private string $attributePath,
        private array $data,
        string $message,
        array $parameters
    ) {
        parent::__construct($message, $parameters);
    }

    public function getErrorCode(): string
    {
        return 'IMPEX_CONVERSION_ERROR';
    }

    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getErrorCount(): int
    {
        return 1;
    }
}
