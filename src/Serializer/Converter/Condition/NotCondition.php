<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class NotCondition extends Condition
{
    public function __construct(private Condition $condition)
    {
    }

    public function match(mixed $value, ConversionContext $context): bool
    {
        return !$this->condition->isFullfilled($value, $context);
    }
}
