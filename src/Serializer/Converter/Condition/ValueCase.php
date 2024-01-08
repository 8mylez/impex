<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\AttributeConverter;

class ValueCase extends ValueIn
{
    public function __construct(array $values, private AttributeConverter $converter, bool $strict = false)
    {
        parent::__construct(null, $values, $strict);
    }

    public function getConverter(): AttributeConverter
    {
        return $this->converter;
    }
}
