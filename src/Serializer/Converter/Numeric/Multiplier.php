<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\ZeroDivisionException;
use Dustin\ImpEx\Util\Type;

class Multiplier extends BidirectionalConverter
{
    /**
     * @var float
     */
    private $factor;

    public function __construct(float $factor, string ...$flags)
    {
        $this->factor = $factor;

        parent::__construct(...$flags);
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $type = Type::getType($value);

        if (!$this->hasFlag(self::STRICT) && !Type::isNumericType($type)) {
            $this->validateNumericConvertable($value, $path, $object->toArray());

            $value = floatval($value);
        }

        $this->validateType($value, Type::NUMERIC, $path, $object->toArray());

        return $value * $this->factor;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $type = Type::getType($value);

        if (!$this->hasFlag(self::STRICT) && !Type::isNumericType($type)) {
            $this->validateNumericConvertable($value, $path, $object->toArray());

            $value = floatval($value);
        }

        $this->validateType($value, Type::NUMERIC, $path, $object->toArray());

        if ($this->factor === 0.0) {
            throw new ZeroDivisionException($path, $data);
        }

        return $value / $this->factor;
    }
}
