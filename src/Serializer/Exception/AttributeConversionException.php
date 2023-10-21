<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCodeException;

class AttributeConversionException extends ErrorCodeException
{
    public function __construct(
        private string $attributePath,
        private array $data,
        private string $errorCode,
        string $message,
        array $parameters
    ) {
        parent::__construct($message, $parameters);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
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

    public function getSummaryMessage(): string
    {
        return $this->getMessage();
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return [$this->getMessage()];
    }
}
