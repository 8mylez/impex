<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;

abstract class Condition
{
    use ProcessValueTrait;

    public function __construct(private mixed $compareValue = null)
    {
        if ($compareValue instanceof AccessOperation && !AccessOperation::isReadOperation($compareValue)) {
            throw new \LogicException('Operation must be read-operation.');
        }
    }

    abstract protected function match(mixed $value, ConversionContext $context): bool;

    public function isFullfilled(mixed $value, ConversionContext $context): bool
    {
        if ($this->compareValue !== null) {
            $value = $this->processValue($this->compareValue, $context);
        }

        return $this->match($value, $context);
    }
}
