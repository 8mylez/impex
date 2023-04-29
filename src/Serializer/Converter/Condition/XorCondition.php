<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;

class XorCondition extends Condition
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
        $fullfilled = 0;

        foreach ($this->conditions as $condition) {
            if ($condition->isFullfilled($value, $object, $path, $attributeName)) {
                ++$fullfilled;

                if ($fullfilled > 1) {
                    return false;
                }
            }
        }

        return $fullfilled === 1;
    }
}
