<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\Condition\Condition;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Util\Type;

class StringContains extends Condition
{
    public const CONTAINS = 0;

    public const STARTS_WITH = 1;

    public const ENDS_WITH = 2;

    public function __construct(mixed $compareValue = null, private string $needle, private int $mode = self::CONTAINS)
    {
        if (!in_array($mode, [self::CONTAINS, self::STARTS_WITH, self::ENDS_WITH])) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid mode.', $mode));
        }

        parent::__construct($compareValue);
    }

    protected function match(mixed $value, ConversionContext $context): bool
    {
        if (!Type::is($value, Type::STRING)) {
            throw InvalidTypeException::invalidType(Type::STRING, $value, $context);
        }

        switch ($this->mode) {
            case self::CONTAINS:
                return str_contains($value, $this->needle);
            case self::STARTS_WITH:
                return str_starts_with($value, $this->needle);
            case self::ENDS_WITH:
                return str_ends_with($value, $this->needle);
        }
    }
}
