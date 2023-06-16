<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;

class NotCondition extends Condition
{
    public function __construct(private Condition $condition)
    {
    }

    public function isFullfilled($value, EncapsulationInterface $object, string $path, string $attributeName): bool
    {
        return !$this->condition->isFullfilled($value, $object, $path, $attributeName);
    }
}
