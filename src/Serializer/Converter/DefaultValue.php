<?php

namespace Dustin\ImpEx\Serializer\Converter;

class DefaultValue extends BidirectionalConverter
{
    private function __construct(
        private mixed $normalizationDefaultValue,
        private mixed $denormalizationDefaultValue
    ) {
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        return $this->normalizationDefaultValue;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        return $this->denormalizationDefaultValue;
    }
}
