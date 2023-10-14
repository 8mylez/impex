<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

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

    public function isFullfilled(mixed $value, ConversionContext $context): bool
    {
        $fullfilled = 0;

        foreach ($this->conditions as $condition) {
            if ($condition->isFullfilled($value, $context)) {
                ++$fullfilled;

                if ($fullfilled > 1) {
                    return false;
                }
            }
        }

        return $fullfilled === 1;
    }
}
