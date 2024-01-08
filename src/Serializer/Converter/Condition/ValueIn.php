<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class ValueIn extends Condition
{
    public function __construct(mixed $compareValue = null, private array $values, private bool $strict = false)
    {
        parent::__construct($compareValue);
    }

    protected function match(mixed $value, ConversionContext $context): bool
    {
        return in_array($value, $this->values, $this->strict);
    }
}
