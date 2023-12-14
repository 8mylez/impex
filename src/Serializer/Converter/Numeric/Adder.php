<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;
use Dustin\ImpEx\Util\Type;

class Adder extends BidirectionalConverter
{
    use ProcessValueTrait;

    private $summand;

    public function __construct(int|float|AccessOperation|AttributeConverter|callable $summand, string ...$flags)
    {
        $this->summand = $summand;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::NUMERIC, $context);

        $summand = $this->processValue($this->summand, $context);

        $this->ensureType($summand, Type::NUMERIC, $context);

        return $value + $summand;
    }

    public function denormalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::NUMERIC, $context);

        $summand = $this->processValue($this->summand, $context);

        $this->ensureType($summand, Type::NUMERIC, $context);

        return $value - $summand;
    }
}
