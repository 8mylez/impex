<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\ZeroDivisionException;
use Dustin\ImpEx\Util\Type;

class Multiplier extends BidirectionalConverter
{
    use NumberConversionTrait;

    /**
     * @var float
     */
    private $factor;

    public function __construct($factor, string ...$flags)
    {
        if (!Type::is($factor, Type::NUMERIC)) {
            throw new \InvalidArgumentException(sprintf('Factor must be integer or float, %s given.', Type::getDebugType($factor)));
        }

        $this->factor = $factor;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT) && !Type::is($value, Type::NUMERIC)) {
            $this->validateNumericConvertable($value, $path, $object->toArray());

            $value = $this->convertToNumeric($value);
        }

        $this->validateType($value, Type::NUMERIC, $path, $object->toArray());

        return $value * $this->factor;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT) && !Type::is($value, Type::NUMERIC)) {
            $this->validateNumericConvertable($value, $path, $object->toArray());

            $value = $this->convertToNumeric($value);
        }

        $this->validateType($value, Type::NUMERIC, $path, $object->toArray());

        if (floatval($this->factor) === 0.0) {
            throw new ZeroDivisionException($path, $data);
        }

        return $value / $this->factor;
    }
}
