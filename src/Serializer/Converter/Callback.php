<?php

namespace Dustin\ImpEx\Serializer\Converter;

class Callback extends BidirectionalConverter
{
    public function __construct(
        private \Closure $normalizationCallback,
        private ?\Closure $denormalizationCallback = null
    ) {
        if ($denormalizationCallback === null) {
            $this->denormalizationCallback = $normalizationCallback;
        }
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        $c = $this->normalizationCallback;

        return $c($value, $context);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        $c = $this->denormalizationCallback;

        return $c($value, $context);
    }
}
