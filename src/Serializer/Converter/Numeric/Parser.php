<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Parser extends UnidirectionalConverter
{
    public const EMPTY_TO_ZERO = 'empty_to_zero';

    public const NUMBER_REQUIRED = 'number_required';

    /**
     * @var string
     */
    private $decimalSeparator;

    /**
     * @var string
     */
    private $thousandsSeparator;

    public function __construct(
        string $decimalSeparator = '.',
        string $thousandsSeparator = ',',
        string ...$flags
    ) {
        if (strlen($decimalSeparator) > 1) {
            throw new \InvalidArgumentException(sprintf("Decimal separator must have length of 1. '%s' given.", $decimalSeparator));
        }

        if (strlen($thousandsSeparator) > 1) {
            throw new \InvalidArgumentException("Thousands separator must have length of 1. '%' given.", $thousandsSeparator);
        }

        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;

        parent::__construct(...$flags);
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $this->validateStringConvertable($value, $path, $data);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $path, $data);

        $value = $this->parseNumber($value);
    }

    public function parseNumber(string $value): ?float
    {
        $value = trim($value);

        if (empty($value) && strlen($value) === 0) {
            return null;
        }

        $number = '';

        foreach (\mb_str_split($value) as $character) {
            if (\is_numeric($character)) {
                $number .= $character;

                continue;
            }

            if ($character !== $this->thousandsSeparator && $character !== $this->decimalSeparator && !empty($number)) {
                break;
            }

            if ($character === $this->thousandsSeparator) {
                if (!$this->isValidThousandSeparator($number)) {
                    break;
                }

                $number .= ',';
                continue;
            }

            if ($character === $this->decimalSeparator) {
                if (!$this->isValidDecimalSeparator($number)) {
                    break;
                }

                $number .= '.';
                continue;
            }
        }

        if (strrpos($number, ',') > strlen($number) - 4) {
            $number = substr($number, 0, strrpos($number, ','));
        }

        $number = str_replace(',', '', $number);

        if (empty($number)) {
            return null;
        }

        return floatval($number);
    }

    private function isValidThousandSeparator(string $number): bool
    {
        if ((empty($number) && strlen($number) === 0) || strpos($number, '.') !== false) {
            return false;
        }

        if (strpos($number, ',') === false) {
            return true;
        }

        return strrpos($number, ',') === (strlen($number) - 4);
    }

    private function isValidDecimalSeparator(string $number): bool
    {
        if (empty($number) && strlen($number) === 0) {
            return true;
        }

        if (strpos($number, '.') === false) {
            return true;
        }

        if (strpos($number, ',') === false) {
            return true;
        }

        return strrpos($number, ',') === (strlen($number) - 4);
    }
}
