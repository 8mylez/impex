<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\PropertyAccess\Path;

class PropertyNotFoundException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS__PROPERTY_NOT_FOUND_ERROR';

    public function __construct(private Path $path, ?string $customMessage = null)
    {
        parent::__construct(
            $customMessage ?? 'Property {{ path }} was not found.',
            ['path' => (string) $path]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
