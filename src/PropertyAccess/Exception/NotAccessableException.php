<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\PropertyAccess\Path;

class NotAccessableException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS__NOT_ACCESSABLE_ERROR';

    public function __construct(private Path $path, private string $type, private string $operation)
    {
        $message = 'Value of type {{ type }} ';

        if (!$path->isEmpty()) {
            $message .= "at '{{ path }}' ";
        }

        $message .= "is not accessable for operation '{{ operation }}'.";

        parent::__construct(
            $message,
            [
                'type' => $type,
                'path' => (string) $path,
                'operation' => $operation,
            ]
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
