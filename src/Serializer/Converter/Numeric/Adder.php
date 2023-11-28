<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class Adder extends BidirectionalConverter
{
    public function __construct(private int|float $summand, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::NUMERIC, $context);

        return $value + $this->summand;
    }

    public function denormalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::NUMERIC, $context);

        return $value - $this->summand;
    }
}
