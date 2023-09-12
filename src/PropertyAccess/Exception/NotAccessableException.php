<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;

class NotAccessableException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS_NOT_ACCESSABLE';

    public function __construct(private string $property, private string $type)
    {
        parent::__construct(
            'Value of type {{ type }} at {{ property }} is not accessable.',
            ['type' => $type, 'property' => $property]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperty(): string
    {
        return array_pop(explode('.', $this->property));
    }

    public function getFullProperty(): string
    {
        return $this->property;
    }

    public function getPropertyAsPath(): array
    {
        return explode('.', $this->property);
    }
}
