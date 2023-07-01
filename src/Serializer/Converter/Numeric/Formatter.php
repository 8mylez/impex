<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Formatter extends UnidirectionalConverter
{
    use NumberConversionTrait;

    public function __construct(
        private string $decimalSeparator = '.',
        private string $thousandsSeparator = ',',
        private int $decimals = 3,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $type = Type::getType($value);

        if (!$this->hasFlag(self::STRICT) && !Type::isNumericType($type)) {
            $this->validateNumericConvertable($value, $path, $data ?? $object->toArray());

            $value = $this->convertToNumeric($value);
        }

        $this->validateType($value, Type::NUMERIC, $path, $data ?? $object->toArray());

        return $this->formatNumber($value);
    }

    public function formatNumber(float $number): string
    {
        return \number_format(
            $number,
            $this->decimals,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );
    }
}
