<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Parser extends UnidirectionalConverter
{
    use NumberConversionTrait;

    /**
     * Flag. If set, zero will returned if no valid number could be found.
     */
    public const EMPTY_TO_ZERO = 'empty_to_zero';

    /**
     * Flag. If set, leading characters will be ignored. E.g. "ABC123" can be parsed to 123.
     */
    public const IGNORE_LEADING_CHARACTERS = 'ignore_leading_characters';

    /**
     * Flag. If set, all characters other than numbers, decimal separators and thousand separators will be ignored.
     * All valid numeric characters will be included in the result. E.g. "ABC123DEF.456" can be parsed to 123.456.
     * This does not include decimal separators and thousand separators. If one of those appears at invalid position the parsing process will stop.
     */
    public const IGNORE_ALL_CHARACTERS = 'ignore_all_characters';

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
        $this->validateSeparators($decimalSeparator, $thousandsSeparator);

        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
            self::EMPTY_TO_ZERO,
            self::IGNORE_LEADING_CHARACTERS,
            self::IGNORE_ALL_CHARACTERS,
        ];
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $this->validateStringConvertable($value, $path, $data ?? $object->toArray());

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $path, $data ?? $object->toArray());

        $value = $this->parseNumber($value);

        if ($value === null && $this->hasFlag(self::EMPTY_TO_ZERO)) {
            $value = 0;
        }

        if (Type::is($value, Type::NUMERIC)) {
            $value = $this->convertToNumeric($value);
        }

        return $value;
    }

    protected function parseNumber(string $value): ?float
    {
        $value = trim($value);

        if ($this->isEmpty($value)) {
            return null;
        }

        $number = '';
        $hasSign = false;

        foreach (\mb_str_split($value) as $character) {
            // If character is numeric append to number string and continue
            if (\is_numeric($character)) {
                $number .= $character;

                continue;
            }

            // If character is prefix sign (+ or -)
            if ($this->isSign($character)) {
                // If $number has no sign yet and is empty
                if (!$hasSign && $this->isEmpty($number)) {
                    $number .= $character;
                    $hasSign = true;

                    continue;
                }

                // If number is a single sign character
                if ($this->isSign($number)) {
                    if ($this->hasOneOfFlags(self::IGNORE_LEADING_CHARACTERS, self::IGNORE_ALL_CHARACTERS)) {
                        $number = $character;
                        $hasSign = true;

                        continue;
                    }

                    break;
                }

                if ($this->hasFlag(self::IGNORE_ALL_CHARACTERS)) {
                    continue;
                }

                break;
            }

            // If character is not a decimal separator or thousand separator
            if (!$this->isMathsCharacter($character)) {
                if ($this->isSign($number)) {
                    if ($this->hasFlag(self::IGNORE_ALL_CHARACTERS)) {
                        continue;
                    }

                    break;
                }

                if ($this->isEmpty($number)) {
                    if ($this->hasOneOfFlags(self::IGNORE_LEADING_CHARACTERS, self::IGNORE_ALL_CHARACTERS)) {
                        continue;
                    }

                    break;
                }

                if ($this->hasFlag(self::IGNORE_ALL_CHARACTERS)) {
                    continue;
                }

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

        // Shorten number if last thousand separator is invalid
        if (strrpos($number, ',') > strlen($number) - 4) {
            $number = substr($number, 0, strrpos($number, ','));
        }

        // Remove all thousand separators
        $number = str_replace(',', '', $number);

        if ($this->isEmpty($number) || $this->isSign($number)) {
            return null;
        }

        return floatval($number);
    }

    private function isValidThousandSeparator(string $number): bool
    {
        if ($this->isEmpty($number) || strpos($number, '.') !== false) {
            return false;
        }

        if (strpos($number, ',') === false) {
            return true;
        }

        return strrpos($number, ',') === (strlen($number) - 4);
    }

    private function isValidDecimalSeparator(string $number): bool
    {
        if ($this->isEmpty($number)) {
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

    private function isMathsCharacter(string $character): bool
    {
        return \in_array($character, ['+', '-', $this->thousandsSeparator, $this->decimalSeparator]);
    }

    private function isSign(string $character): bool
    {
        return \in_array($character, ['+', '-']);
    }

    private function isEmpty(string $number): bool
    {
        return empty($number) && strlen($number) === 0;
    }

    private function validateSeparators(string $decimalSeparator, string $thousandsSeparator): void
    {
        if (strlen($decimalSeparator) > 1) {
            throw new \InvalidArgumentException(sprintf("Decimal separator must have length of 1. '%s' given.", $decimalSeparator));
        }

        if ($this->isSign($decimalSeparator)) {
            throw new \InvalidArgumentException(sprintf("'%s' is not allowed as decimal separator.", $decimalSeparator));
        }

        if (strlen($thousandsSeparator) > 1) {
            throw new \InvalidArgumentException("Thousands separator must have length of 1. '%' given.", $thousandsSeparator);
        }

        if ($this->isSign($thousandsSeparator)) {
            throw new \InvalidArgumentException(sprintf("'%s' is not allowed as thousands separator.", $thousandsSeparator));
        }

        if ($decimalSeparator === $thousandsSeparator) {
            throw new \LogicException(sprintf('Decimal separator and thousand separator cannot be the same. Both (%s) given.', $decimalSeparator));
        }
    }
}
