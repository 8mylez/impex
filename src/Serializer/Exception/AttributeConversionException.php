<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCode;
use Dustin\Exception\ErrorCodeException;

class AttributeConversionException extends ErrorCodeException
{
    public const UNKNOWN_ERROR = 'IMPEX_CONVERSION__UNKNOWN_ERROR';

    public function __construct(
        private string $attributePath,
        private array $data,
        string $message,
        array $parameters,
        private string $errorCode
    ) {
        parent::__construct($message, $parameters);
    }

    public static function unknown(string $path, array $data, string $message): self
    {
        return new self(
            $path, $data,
            $message, [],
            self::UNKNOWN_ERROR
        );
    }

    public static function fromErrorCode(string $path, array $data, ErrorCode $errorCode): self
    {
        return new self(
            $path, $data,
            $errorCode->getMessage(), [],
            $errorCode->getErrorCode()
        );
    }

    public static function fromException(string $path, array $data, \Throwable $th): self
    {
        return static::unknown($path, $data, $th->getMessage());
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
