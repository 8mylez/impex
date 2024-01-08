<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class IsEmpty extends Condition
{
    public function match(mixed $value, ConversionContext $context): bool
    {
        return empty($value);
    }
}
