<?php

namespace Dustin\ImpEx\Serializer\Exception;

class ZeroDivisionException extends AttributeConversionException
{
    public function __construct(
        string $path,
        array $data
    ) {
        parent::__construct(
            $path, $data,
            sprintf('Division by zero was detected.')
        );
    }
}
