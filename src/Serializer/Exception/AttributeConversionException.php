<?php

namespace Dustin\ImpEx\Serializer\Exception;

class AttributeConversionException extends \Exception
{
    public function __construct(
        private string $attributePath,
        private array $data,
        string $message
    ) {
        parent::__construct($message);
    }

    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
