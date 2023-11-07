<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class NameConverterArrayKeyConversionStrategy extends ArrayKeyConversionStrategy
{
    public function __construct(private NameConverterInterface $nameConverter)
    {
    }

    public function normalizeKeys(array $data, ConversionContext $context): array
    {
        $converted = [];

        foreach ($data as $key => $value) {
            $converted[$this->nameConverter->normalize($key)] = $value;
        }

        return $converted;
    }

    public function denormalizeKeys(array $data, ConversionContext $context): array
    {
        $converted = [];

        foreach ($data as $key => $value) {
            $converted[$this->nameConverter->denormalize($key)] = $value;
        }

        return $converted;
    }
}
