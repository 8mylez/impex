<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCode;

interface AttributeConversionExceptionInterface extends ErrorCode
{
    public function getAttributePath(): string;

    public function getData(): array;

    public function getMessages(): array;
}
