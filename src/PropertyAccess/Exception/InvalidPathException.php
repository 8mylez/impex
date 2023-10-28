<?php

namespace Dustin\ImpEx\PropertyAccess\Exception;

use Dustin\Exception\ErrorCodeException;

class InvalidPathException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_PROPERTY_ACCESS__INVALID_PATH_ERROR';

    public static function unexpectedCharacter(string $path, string $character, int $position): self
    {
        return new self(
            'Could not parse path "{{ path }}". Unexpected character "{{ character }}" at position {{ position }}',
            ['path' => $path, 'character' => $character, 'position' => $position]
        );
    }

    public static function emptyField(?int $position = null): self
    {
        $message = 'Field ';
        $params = [];

        if ($position !== null) {
            $message .= 'at position {{ position }} ';
            $params['position'] = $position;
        }

        $message .= 'cannot be empty.';

        return new self($message, $params);
    }

    public static function emptyPath(): self
    {
        return new self('Path cannot be empty.', []);
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
