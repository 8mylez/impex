<?php

namespace Dustin\ImpEx\Sequence\Exception;

use Dustin\Exception\ErrorCodeException;

class SectionNotFoundException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_SECTION_NOT_FOUND';

    public function __construct(string $id)
    {
        parent::__construct(
            'Section with id {{ id }} was not found.',
            ['id' => $id]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
