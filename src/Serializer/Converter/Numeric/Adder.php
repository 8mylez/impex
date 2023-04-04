<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Adder extends BidirectionalConverter
{
    /**
     * @var float
     */
    private $summand;

    public function __construct(float $summand, string ...$flags)
    {
        $this->summand = $summand;

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

        return $value + $this->summand;
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

        return $value - $this->summand;
    }
}
