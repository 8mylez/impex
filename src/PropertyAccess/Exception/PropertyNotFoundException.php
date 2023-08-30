<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;

class PropertyNotFoundException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS_PROPERTY_NOT_FOUND';

    public function __construct(private string $property, ?string $customMessage = null)
    {
        parent::__construct(
            $customMessage ?? 'Property {{ property }} was not found.',
            ['property' => $property]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
