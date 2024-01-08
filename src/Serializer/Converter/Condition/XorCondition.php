<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class XorCondition extends Condition
{
    /**
     * @var array
     */
    private $conditions = [];

    public function __construct(mixed $compareValue = null, Condition ...$conditions)
    {
        $this->conditions = $conditions;

        parent::__construct($compareValue);
    }

    public function match(mixed $value, ConversionContext $context): bool
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
