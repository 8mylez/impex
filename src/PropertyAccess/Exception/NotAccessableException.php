<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\PropertyAccess\Path;

class NotAccessableException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS_NOT_ACCESSABLE';

    public function __construct(private Path $path, private string $type)
    {
        $message = 'Value of type {{ type }} ';

        if (!$path->isEmpty()) {
            $message .= "at '{{ path }}' ";
        }

        $message .= 'is not accessable.';

        parent::__construct(
            $message,
            ['type' => $type, 'path' => (string) $path]
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

    public function getProperty(): ?string
    {
        return array_pop($this->path->toArray());
    }

    public function getPath(): Path
    {
        return $this->path;
    }
}
