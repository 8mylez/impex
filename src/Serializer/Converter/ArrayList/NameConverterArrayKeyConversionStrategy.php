<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class NameConverterArrayKeyConversionStrategy extends ArrayKeyConversionStrategy
{
    public function __construct(private NameConverterInterface $nameConverter)
    {
    }

    public function normalizeKeys(array $keys, ConversionContext $context): array
    {
        return array_map(
            function (string|int $key) {
                return $this->nameConverter->normalize($key);
            },
            $keys
        );
    }

    public function denormalizeKeys(array $keys, ConversionContext $context): array
    {
        return array_map(
            function (string|int $key) {
                return $this->nameConverter->denormalize($key);
            },
            $keys
        );
    }
}
