<?php

namespace Dustin\ImpEx\Serializer\Exception;

class AttributeConversionException extends \Exception
{
    /**
     * @var string
     */
    private $attributePath;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $attributePath, array $data, string $message)
    {
        $this->attributePath = $attributePath;
        $this->data = $data;

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
