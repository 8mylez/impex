<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCode;
use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class AttributeConversionException extends ErrorCodeException implements AttributeConversionExceptionInterface
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

    public static function unknown(string $message, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            $message, [],
            self::UNKNOWN_ERROR
        );
    }

    public static function fromErrorCode(ErrorCode $errorCode, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            $errorCode->getMessage(), [],
            $errorCode->getErrorCode()
        );
    }

    public static function fromException(\Throwable $th, ConversionContext $context): self
    {
        return static::unknown($th->getMessage(), $context);
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
