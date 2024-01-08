<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class IsDirection extends Condition
{
    public function __construct(private string $direction)
    {
    }

    public function match(mixed $value, ConversionContext $context): bool
    {
        return $context->getDirection() === $this->direction;
    }
}
