<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class CaseConverter extends BidirectionalConverter
{
    public const FIRST_LETTER_ONLY = 'first_letter_only';

    public function __construct(private int $normalizedCase = CASE_LOWER, string ...$flags)
    {
        if ($normalizedCase !== CASE_LOWER && $normalizedCase !== CASE_UPPER) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid case type. Use constants CASE_LOWER and CASE_UPPER.', $normalizedCase));
        }

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if ($this->normalizedCase === CASE_LOWER) {
            if ($this->hasFlags(self::FIRST_LETTER_ONLY)) {
                return lcfirst($value);
            }

            return strtolower($value);
        }

        if ($this->hasFlags(self::FIRST_LETTER_ONLY)) {
            return ucfirst($value);
        }

        return strtoupper($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if ($this->normalizedCase === CASE_UPPER) {
            if ($this->hasFlags(self::FIRST_LETTER_ONLY)) {
                return lcfirst($value);
            }

            return strtolower($value);
        }

        if ($this->hasFlags(self::FIRST_LETTER_ONLY)) {
            return ucfirst($value);
        }

        return strtoupper($value);
    }
}
