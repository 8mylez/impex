<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class IsType extends Condition
{
    public function __construct(mixed $compareValue = null, private string $type)
    {
        parent::__construct($compareValue);
    }

    public function match(mixed $value, ConversionContext $context): bool
    {
        return Type::is($value, $this->type);
    }
}
