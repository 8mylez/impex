<?php

namespace Dustin\ImpEx\Sequence\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\ImpEx\Sequence\Sequence;

class SequenceBuildException extends ErrorCodeException
{
    public const ERROR_CODE = 'IMPEX_SEQUENCE_BUILD_FAILED';

    public const ERROR_CODE_RECORD_HANDLER_NOT_FOUND = 'IMPEX_RECORD_HANDLER_NOT_FOUND';

    public const ERROR_CODE_UNKNOWN_SECTION_TYPE = 'IMPEX_UNKNOWN_SECTION_TYPE';

    public const ERROR_CODE_INVALID_SEQUENCE_CLASS = 'IMPEX_INVALID_SEQUENCE_CLASS';

    private $errorCode = self::ERROR_CODE;

    public static function recordHandlerNotFound(string $id): self
    {
        $exception = new self(
            'Record handler with id {{ id }} was not found.',
            ['id' => $id]
        );

        $exception->errorCode = self::ERROR_CODE_RECORD_HANDLER_NOT_FOUND;

        return $exception;
    }

    public static function unknownSectionType(string $type): self
    {
        $exception = new self(
            'Section type {{ type }} is unknown.',
            ['type' => $type]
        );

        $exception->errorCode = self::ERROR_CODE_UNKNOWN_SECTION_TYPE;

        return $exception;
    }

    public static function invalidSequenceClass(string $class): self
    {
        $exception = new self(
            'Sequence class must inherit from {{ abstractClass }}. {{ class }} given.',
            ['abstractClass' => Sequence::class, 'class' => $class]
        );

        $exception->errorCode = self::ERROR_CODE_INVALID_SEQUENCE_CLASS;

        return $exception;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
