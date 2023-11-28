<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class Comparison extends Condition
{
    public const EQUALS = 'equals';

    public const IDENTICAL = 'identical';

    public const NOT_EQUALS = 'not_equals';

    public const NOT_IDENTICAL = 'not_identical';

    public const LESS_THAN = 'less_than';

    public const GREATER_THAN = 'greater_than';

    public const LESS_THAN_OR_EQUALS = 'less_than_or_equals';

    public const GREATER_THAN_OR_EQUALS = 'greater_than_or_equals';

    public const OPERATORS = [
        self::EQUALS,
        self::IDENTICAL,
        self::NOT_EQUALS,
        self::NOT_IDENTICAL,
        self::LESS_THAN,
        self::GREATER_THAN,
        self::LESS_THAN_OR_EQUALS,
        self::GREATER_THAN_OR_EQUALS,
    ];

    public function __construct(mixed $primaryCompareValue = null, private string $operator, private mixed $secondaryCompareValue)
    {
        if (!in_array($operator, self::OPERATORS)) {
            throw new \InvalidArgumentException(sprintf("'%s' is not a valid operator.", $operator));
        }

        parent::__construct($primaryCompareValue);
    }

    public function match(mixed $value, ConversionContext $context): bool
    {
        $compareValue = $this->processValue($this->secondaryCompareValue, $context);

        switch ($this->operator) {
            case self::EQUALS:
                return $value == $compareValue;
            case self::IDENTICAL:
                return $value === $compareValue;
            case self::NOT_EQUALS:
                return $value != $compareValue;
            case self::NOT_IDENTICAL:
                return $value !== $compareValue;
            case self::LESS_THAN:
                return $value < $compareValue;
            case self::GREATER_THAN:
                return $value > $compareValue;
            case self::LESS_THAN_OR_EQUALS:
                return $value <= $compareValue;
            case self::GREATER_THAN_OR_EQUALS:
                return $value >= $compareValue;
        }

        return false;
    }
}
