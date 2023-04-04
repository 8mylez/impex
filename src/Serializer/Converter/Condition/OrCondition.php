<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;

class OrCondition extends Condition
{
    /**
     * @var array
     */
    private $conditions = [];

    public function __construct(Condition ...$conditions)
    {
        $this->conditions = $conditions;
    }

    public function isFullfilled($value, EncapsulationInterface $object, string $path, string $attributeName): bool
    {
        foreach ($this->conditions as $condition) {
            if ($condition->isFullfilled($value, $object, $path, $attributeName)) {
                return true;
            }
        }

        return false;
    }
}
