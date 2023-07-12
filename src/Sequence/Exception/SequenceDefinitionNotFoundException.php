<?php

namespace Dustin\ImpEx\Sequence\Exception;

use Dustin\Exception\ErrorCodeException;

class SequenceDefinitionNotFoundException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_SEQUENCE_NOT_FOUND';

    public function __construct(string $id)
    {
        parent::__construct(
            'Sequence definition for id {{ id }} was not found.',
            ['id' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
