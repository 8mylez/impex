<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
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

    public function convert(mixed $value, ConversionContext $context): string|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $type = Type::getType($value);

        if (!$this->hasFlags(self::STRICT) && !Type::isNumericType($type)) {
            $this->validateNumericConvertable($value, $context);

            $value = $this->convertToNumeric($value);
        }

        $this->validateType($value, Type::NUMERIC, $context);

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
