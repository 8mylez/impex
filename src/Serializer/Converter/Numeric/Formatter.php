<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Formatter extends UnidirectionalConverter
{
    use NumberConversionTrait;

    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var string
     */
    private $thousandsSeparator;

    /**
     * @var int
     */
    private $decimals;

    public function __construct(
        string $decimalSeparator = '.',
        string $thousandsSeparator = ',',
        int $decimals = 3,
        string ...$flags
    ) {
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimals = $decimals;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
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
