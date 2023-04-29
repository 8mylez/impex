<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Adder extends BidirectionalConverter
{
    use NumberConversionTrait;

    /**
     * @var float
     */
    private $summand;

    public function __construct($summand, string ...$flags)
    {
        if (!Type::is($summand, Type::NUMERIC)) {
            throw new \InvalidArgumentException(sprintf('Summand must be integer or float, %s given.', Type::getDebugType($summand)));
        }

        $this->summand = $summand;

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

        return $value + $this->summand;
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

        return $value - $this->summand;
    }
}
