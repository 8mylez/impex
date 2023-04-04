<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class InvalidTypeException extends AttributeConversionException
{
    public function __construct(string $path, array $data, string $expectedType, $value)
    {
        parent::__construct(
            $path, $data,
            sprintf('Value must be %s. %s given.', $expectedType, Type::getDebugType($value))
        );
    }
}
