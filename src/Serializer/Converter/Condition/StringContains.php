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

    public function __construct(mixed $compareValue = null, private string $needle, private int $mode = self::CONTAINS, private bool $ignoreCase = false)
    {
        if (!in_array($mode, [self::CONTAINS, self::STARTS_WITH, self::ENDS_WITH])) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid mode.', $mode));
        }

        if ($ignoreCase === true) {
            $this->needle = strtolower($needle);
        }

        parent::__construct($compareValue);
    }

    protected function match(mixed $value, ConversionContext $context): bool
    {
        if (!Type::is($value, Type::STRING)) {
            throw InvalidTypeException::invalidType(Type::STRING, $value, $context);
        }

        $haystack = $value;

        if ($this->ignoreCase === true) {
            $haystack = strtolower($haystack);
        }

        switch ($this->mode) {
            case self::CONTAINS:
                return str_contains($haystack, $this->needle);
            case self::STARTS_WITH:
                return str_starts_with($haystack, $this->needle);
            case self::ENDS_WITH:
                return str_ends_with($haystack, $this->needle);
        }
    }
}
