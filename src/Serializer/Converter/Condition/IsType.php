<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class IsType extends Condition
{
    public function __construct(private string $type)
    {
    }

    public function isFullfilled(mixed $value, ConversionContext $context): bool
    {
        return Type::is($value, $this->type);
    }
}
