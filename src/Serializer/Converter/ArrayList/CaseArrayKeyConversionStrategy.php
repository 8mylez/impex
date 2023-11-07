<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class CaseArrayKeyConversionStrategy extends ArrayKeyConversionStrategy
{
    public function __construct(private int $normalizedCase = CASE_LOWER)
    {
        if ($normalizedCase !== CASE_LOWER && $normalizedCase !== CASE_UPPER) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid case type. Use constants CASE_LOWER and CASE_UPPER.', $normalizedCase));
        }
    }

    public function normalizeKeys(array $keys, ConversionContext $context): array
    {
        return array_keys(array_change_key_case(array_flip($keys), $this->normalizedCase));
    }

    public function denormalizeKeys(array $keys, ConversionContext $context): array
    {
        return array_keys(array_change_key_case(array_flip($keys), $this->normalizedCase === CASE_LOWER ? CASE_UPPER : CASE_LOWER));
    }
}
