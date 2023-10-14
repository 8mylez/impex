<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class AndCondition extends Condition
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
        if (empty($this->conditions)) {
            return false;
        }

        foreach ($this->conditions as $condition) {
            if (!$condition->isFullfilled($value, $context)) {
                return false;
            }
        }

        return true;
    }
}
