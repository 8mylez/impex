<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Util\Type;

class StringConversionException extends AttributeConversionException
{
    public function __construct(
        $value,
        string $path,
        array $data
    ) {
        parent::__construct(
            $path, $data,
            \sprintf('Value of type %s cannot be converted to string.', Type::getDebugType($value))
        );
    }
}
