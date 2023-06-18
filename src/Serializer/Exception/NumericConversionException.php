<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class NumericConversionException extends AttributeConversionException
{
    public function __construct($value, string $path, array $data)
    {
        parent::__construct(
            $path, $data,
            \sprintf('Value of type %s cannot be converted to int or float.', Type::getDebugType($value))
        );
    }
}
