<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class CaseArrayKeyConversionStrategy extends ArrayKeyConversionStrategy
{
    public function __construct(private int $normalizedCase = CASE_LOWER)
    {
    }

    public function normalizeKeys(array $data, ConversionContext $context): array
    {
        return array_change_key_case($data, $this->normalizedCase);
    }

    public function denormalizeKeys(array $data, ConversionContext $context): array
    {
        return array_change_key_case($data, $this->normalizedCase === CASE_LOWER ? CASE_UPPER : CASE_LOWER);
    }
}
