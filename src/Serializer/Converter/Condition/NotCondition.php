<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;

class NotCondition extends Condition
{
    /**
     * @var Condition
     */
    private $condition;

    public function __construct(Condition $condition)
    {
        $this->condition = $condition;
    }

    public function isFullfilled($value, EncapsulationInterface $object, string $path, string $attributeName): bool
    {
        return !$this->condition->isFullfilled($value, $object, $path, $attributeName);
    }
}
