<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Util\Type;

class Multiplier extends BidirectionalConverter
{
    use ProcessValueTrait;

    public const DIVISION_BY_ZERO_ERROR = 'IMPEX_CONVERSION__DIVISION_BY_ZERO_ERROR';

    private $factor;

    public function __construct(int|float|AccessOperation|AttributeConverter|callable $factor, string ...$flags)
    {
        $this->factor = $factor;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::NUMERIC, $context);

        $factor = $this->processValue($this->factor, $context);
        $factor = $this->ensureType($factor, Type::NUMERIC, $context);

        return $value * $factor;
    }

    public function denormalize(mixed $value, ConversionContext $context): int|float|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::NUMERIC, $context);

        $factor = $this->processValue($this->factor, $context);
        $factor = $this->ensureType($factor, Type::NUMERIC, $context);

        if (floatval($factor) === 0.0) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Division by zero was detected.', [], self::DIVISION_BY_ZERO_ERROR);
        }

        return $value / $factor;
    }
}
